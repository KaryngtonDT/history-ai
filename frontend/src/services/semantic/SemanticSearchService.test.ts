import { describe, expect, it, vi } from "vitest";
import type { SemanticSearchRepository } from "./SemanticSearchRepository";
import { SemanticSearchService } from "./SemanticSearchService";

const results = [
	{
		artifactId: "550e8400-e29b-41d4-a716-446655440002",
		chunkId: "550e8400-e29b-41d4-a716-446655440010",
		position: 0,
		text: "## Ancient Rome\n753 BC — Foundation of Rome",
		score: 0.87,
	},
];

function createRepositoryMock(
	overrides: Partial<SemanticSearchRepository> = {},
): SemanticSearchRepository {
	return {
		searchSemanticChunks: vi.fn().mockResolvedValue([]),
		...overrides,
	};
}

describe("SemanticSearchService", () => {
	it("returns semantic results from repository", async () => {
		const searchSemanticChunks = vi.fn().mockResolvedValue(results);
		const service = new SemanticSearchService(
			createRepositoryMock({ searchSemanticChunks }),
		);

		const response = await service.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(searchSemanticChunks).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);
		expect(response).toEqual(results);
	});

	it("returns empty array when repository returns no results", async () => {
		const searchSemanticChunks = vi.fn().mockResolvedValue([]);
		const service = new SemanticSearchService(
			createRepositoryMock({ searchSemanticChunks }),
		);

		const response = await service.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(response).toEqual([]);
	});

	it("returns empty array for empty content id without calling repository", async () => {
		const searchSemanticChunks = vi.fn();
		const service = new SemanticSearchService(
			createRepositoryMock({ searchSemanticChunks }),
		);

		const response = await service.searchSemanticChunks("", "rome");

		expect(searchSemanticChunks).not.toHaveBeenCalled();
		expect(response).toEqual([]);
	});

	it("returns empty array for empty query without calling repository", async () => {
		const searchSemanticChunks = vi.fn();
		const service = new SemanticSearchService(
			createRepositoryMock({ searchSemanticChunks }),
		);

		const response = await service.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"",
		);

		expect(searchSemanticChunks).not.toHaveBeenCalled();
		expect(response).toEqual([]);
	});

	it("returns empty array for invalid content id without calling repository", async () => {
		const searchSemanticChunks = vi.fn();
		const service = new SemanticSearchService(
			createRepositoryMock({ searchSemanticChunks }),
		);

		const response = await service.searchSemanticChunks("content-1", "rome");

		expect(searchSemanticChunks).not.toHaveBeenCalled();
		expect(response).toEqual([]);
	});

	it("trims content id and query before delegating to repository", async () => {
		const searchSemanticChunks = vi.fn().mockResolvedValue([]);
		const service = new SemanticSearchService(
			createRepositoryMock({ searchSemanticChunks }),
		);

		await service.searchSemanticChunks(
			"  550e8400-e29b-41d4-a716-446655440000  ",
			"  rome  ",
		);

		expect(searchSemanticChunks).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);
	});

	it("preserves backend result order from repository", async () => {
		const orderedResults = [
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				chunkId: "550e8400-e29b-41d4-a716-446655440010",
				position: 0,
				text: "Higher score chunk",
				score: 0.95,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				chunkId: "550e8400-e29b-41d4-a716-446655440011",
				position: 1,
				text: "Lower score chunk",
				score: 0.72,
			},
		];
		const searchSemanticChunks = vi.fn().mockResolvedValue(orderedResults);
		const service = new SemanticSearchService(
			createRepositoryMock({ searchSemanticChunks }),
		);

		const response = await service.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(response).toEqual(orderedResults);
		expect(response[0]?.score).toBeGreaterThanOrEqual(response[1]?.score ?? 0);
	});
});
