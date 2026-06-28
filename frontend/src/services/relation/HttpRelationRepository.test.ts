import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpRelationRepository } from "./HttpRelationRepository";

describe("HttpRelationRepository", () => {
	it("uses GET /api/contents/{contentId}/relations", async () => {
		const get = vi.fn().mockResolvedValue({
			relations: [
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "derived_from",
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRelationRepository(httpClient);

		await repository.getArtifactRelations(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(get).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/relations",
		);
	});

	it("maps API DTO to artifact relations", async () => {
		const get = vi.fn().mockResolvedValue({
			relations: [
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "derived_from",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440003",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440002",
					type: "references",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440004",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440005",
					type: "related",
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRelationRepository(httpClient);

		const relations = await repository.getArtifactRelations(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(relations).toEqual([
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
				type: "derived_from",
			},
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440003",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440002",
				type: "references",
			},
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440004",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440005",
				type: "related",
			},
		]);
	});

	it("returns empty array when API returns no relations", async () => {
		const get = vi.fn().mockResolvedValue({ relations: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRelationRepository(httpClient);

		const relations = await repository.getArtifactRelations(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(relations).toEqual([]);
	});

	it("returns empty array when content id is invalid on the server", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 400));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRelationRepository(httpClient);

		const relations = await repository.getArtifactRelations(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(relations).toEqual([]);
	});

	it("propagates non-400 HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpRelationRepository(httpClient);

		await expect(
			repository.getArtifactRelations("550e8400-e29b-41d4-a716-446655440000"),
		).rejects.toBeInstanceOf(ApiError);
	});
});
