import { describe, expect, it, vi } from "vitest";
import type { RecommendationRepository } from "./RecommendationRepository";
import { RecommendationService } from "./RecommendationService";

const recommendations = [
	{
		artifactId: "550e8400-e29b-41d4-a716-446655440001",
		type: "transcript" as const,
		title: "Transcript",
		reason: "derived_from" as const,
	},
	{
		artifactId: "550e8400-e29b-41d4-a716-446655440003",
		type: "quiz" as const,
		title: "Quiz",
		reason: "references" as const,
	},
];

function createRepositoryMock(
	overrides: Partial<RecommendationRepository> = {},
): RecommendationRepository {
	return {
		getArtifactRecommendations: vi.fn().mockResolvedValue([]),
		...overrides,
	};
}

describe("RecommendationService", () => {
	it("returns recommendations from repository", async () => {
		const getArtifactRecommendations = vi
			.fn()
			.mockResolvedValue(recommendations);
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		const result = await service.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(getArtifactRecommendations).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);
		expect(result).toEqual(recommendations);
	});

	it("returns empty array when repository returns no recommendations", async () => {
		const getArtifactRecommendations = vi.fn().mockResolvedValue([]);
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		const result = await service.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(result).toEqual([]);
	});

	it("returns empty array for empty content id without calling repository", async () => {
		const getArtifactRecommendations = vi.fn();
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		const result = await service.getArtifactRecommendations(
			"",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(getArtifactRecommendations).not.toHaveBeenCalled();
		expect(result).toEqual([]);
	});

	it("returns empty array for empty artifact id without calling repository", async () => {
		const getArtifactRecommendations = vi.fn();
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		const result = await service.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"",
		);

		expect(getArtifactRecommendations).not.toHaveBeenCalled();
		expect(result).toEqual([]);
	});

	it("returns empty array for invalid content id without calling repository", async () => {
		const getArtifactRecommendations = vi.fn();
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		const result = await service.getArtifactRecommendations(
			"content-1",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(getArtifactRecommendations).not.toHaveBeenCalled();
		expect(result).toEqual([]);
	});

	it("returns empty array for invalid artifact id without calling repository", async () => {
		const getArtifactRecommendations = vi.fn();
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		const result = await service.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"artifact-summary-1",
		);

		expect(getArtifactRecommendations).not.toHaveBeenCalled();
		expect(result).toEqual([]);
	});

	it("trims content and artifact ids before delegating to repository", async () => {
		const getArtifactRecommendations = vi.fn().mockResolvedValue([]);
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		await service.getArtifactRecommendations(
			"  550e8400-e29b-41d4-a716-446655440000  ",
			"  550e8400-e29b-41d4-a716-446655440002  ",
		);

		expect(getArtifactRecommendations).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);
	});

	it("supports all recommendation reasons from repository", async () => {
		const allReasons = [
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440001",
				type: "transcript" as const,
				title: "Transcript",
				reason: "related" as const,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				type: "summary" as const,
				title: "Summary",
				reason: "derived_from" as const,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440003",
				type: "quiz" as const,
				title: "Quiz",
				reason: "references" as const,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				type: "timeline" as const,
				title: "Timeline",
				reason: "next" as const,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440005",
				type: "flashcards" as const,
				title: "Flashcards",
				reason: "previous" as const,
			},
		];
		const getArtifactRecommendations = vi.fn().mockResolvedValue(allReasons);
		const service = new RecommendationService(
			createRepositoryMock({ getArtifactRecommendations }),
		);

		const result = await service.getArtifactRecommendations(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440010",
		);

		expect(result).toEqual(allReasons);
	});
});
