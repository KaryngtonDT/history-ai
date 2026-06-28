import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpSemanticSearchRepository } from "./HttpSemanticSearchRepository";

describe("HttpSemanticSearchRepository", () => {
	it("uses GET /api/contents/{contentId}/semantic-search with encoded query", async () => {
		const get = vi.fn().mockResolvedValue({ results: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSemanticSearchRepository(httpClient);

		await repository.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"ancient rome",
		);

		expect(get).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/semantic-search?q=ancient%20rome",
		);
	});

	it("maps API DTO to retrieved chunks", async () => {
		const get = vi.fn().mockResolvedValue({
			results: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					position: 0,
					text: "## Ancient Rome\n753 BC — Foundation of Rome",
					score: 0.87,
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440004",
					chunkId: "550e8400-e29b-41d4-a716-446655440011",
					position: 1,
					text: "## Greek history\nClassical period overview",
					score: 0.62,
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSemanticSearchRepository(httpClient);

		const result = await repository.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(result).toEqual([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				chunkId: "550e8400-e29b-41d4-a716-446655440010",
				position: 0,
				text: "## Ancient Rome\n753 BC — Foundation of Rome",
				score: 0.87,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				chunkId: "550e8400-e29b-41d4-a716-446655440011",
				position: 1,
				text: "## Greek history\nClassical period overview",
				score: 0.62,
			},
		]);
	});

	it("preserves backend result order", async () => {
		const get = vi.fn().mockResolvedValue({
			results: [
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
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSemanticSearchRepository(httpClient);

		const result = await repository.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(result[0]?.text).toBe("Higher score chunk");
		expect(result[1]?.text).toBe("Lower score chunk");
	});

	it("omits results with invalid score", async () => {
		const get = vi.fn().mockResolvedValue({
			results: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					position: 0,
					text: "Valid chunk",
					score: 0.87,
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440004",
					chunkId: "550e8400-e29b-41d4-a716-446655440011",
					position: 1,
					text: "Invalid score chunk",
					score: 1.5,
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSemanticSearchRepository(httpClient);

		const result = await repository.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(result).toEqual([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				chunkId: "550e8400-e29b-41d4-a716-446655440010",
				position: 0,
				text: "Valid chunk",
				score: 0.87,
			},
		]);
	});

	it("returns empty array when API returns no results", async () => {
		const get = vi.fn().mockResolvedValue({ results: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSemanticSearchRepository(httpClient);

		const result = await repository.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(result).toEqual([]);
	});

	it("returns empty array when request is invalid on the server", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 400));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSemanticSearchRepository(httpClient);

		const result = await repository.searchSemanticChunks(
			"550e8400-e29b-41d4-a716-446655440000",
			"rome",
		);

		expect(result).toEqual([]);
	});

	it("propagates non-400 HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSemanticSearchRepository(httpClient);

		await expect(
			repository.searchSemanticChunks(
				"550e8400-e29b-41d4-a716-446655440000",
				"rome",
			),
		).rejects.toBeInstanceOf(ApiError);
	});
});
