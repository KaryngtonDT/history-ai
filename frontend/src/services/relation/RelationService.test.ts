import { describe, expect, it, vi } from "vitest";
import type { RelationRepository } from "./RelationRepository";
import { RelationService } from "./RelationService";

const relations = [
	{
		sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
		targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
		type: "derived_from" as const,
	},
];

function createRepositoryMock(
	overrides: Partial<RelationRepository> = {},
): RelationRepository {
	return {
		getArtifactRelations: vi.fn().mockResolvedValue([]),
		...overrides,
	};
}

describe("RelationService", () => {
	it("returns relations from repository", async () => {
		const getArtifactRelations = vi.fn().mockResolvedValue(relations);
		const service = new RelationService(
			createRepositoryMock({ getArtifactRelations }),
		);

		const result = await service.getArtifactRelations(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(getArtifactRelations).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
		expect(result).toEqual(relations);
	});

	it("returns empty array when repository returns no relations", async () => {
		const getArtifactRelations = vi.fn().mockResolvedValue([]);
		const service = new RelationService(
			createRepositoryMock({ getArtifactRelations }),
		);

		const result = await service.getArtifactRelations(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual([]);
	});

	it("returns empty array for empty content id without calling repository", async () => {
		const getArtifactRelations = vi.fn();
		const service = new RelationService(
			createRepositoryMock({ getArtifactRelations }),
		);

		const result = await service.getArtifactRelations("");

		expect(getArtifactRelations).not.toHaveBeenCalled();
		expect(result).toEqual([]);
	});

	it("returns empty array for whitespace-only content id without calling repository", async () => {
		const getArtifactRelations = vi.fn();
		const service = new RelationService(
			createRepositoryMock({ getArtifactRelations }),
		);

		const result = await service.getArtifactRelations("   ");

		expect(getArtifactRelations).not.toHaveBeenCalled();
		expect(result).toEqual([]);
	});

	it("returns empty array for invalid content id without calling repository", async () => {
		const getArtifactRelations = vi.fn();
		const service = new RelationService(
			createRepositoryMock({ getArtifactRelations }),
		);

		const result = await service.getArtifactRelations("content-1");

		expect(getArtifactRelations).not.toHaveBeenCalled();
		expect(result).toEqual([]);
	});

	it("trims content id before delegating to repository", async () => {
		const getArtifactRelations = vi.fn().mockResolvedValue(relations);
		const service = new RelationService(
			createRepositoryMock({ getArtifactRelations }),
		);

		await service.getArtifactRelations(
			"  550e8400-e29b-41d4-a716-446655440000  ",
		);

		expect(getArtifactRelations).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
	});

	it("supports all relation types from repository", async () => {
		const allTypes = [
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440001",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440002",
				type: "related" as const,
			},
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440003",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440004",
				type: "derived_from" as const,
			},
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440005",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440006",
				type: "references" as const,
			},
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440007",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440008",
				type: "next" as const,
			},
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440009",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440010",
				type: "previous" as const,
			},
		];
		const getArtifactRelations = vi.fn().mockResolvedValue(allTypes);
		const service = new RelationService(
			createRepositoryMock({ getArtifactRelations }),
		);

		const result = await service.getArtifactRelations(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual(allTypes);
	});
});
