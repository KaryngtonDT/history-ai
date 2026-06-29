import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpGraphRepository } from "./HttpGraphRepository";
import { EMPTY_KNOWLEDGE_GRAPH } from "./types";

describe("HttpGraphRepository", () => {
	it("uses GET /api/contents/{contentId}/graph", async () => {
		const get = vi.fn().mockResolvedValue({ nodes: [], edges: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		await repository.getKnowledgeGraph("550e8400-e29b-41d4-a716-446655440000");

		expect(get).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/graph",
		);
	});

	it("maps API DTO to knowledge graph", async () => {
		const get = vi.fn().mockResolvedValue({
			nodes: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "transcript",
					title: "Transcript",
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					type: "summary",
					title: "Summary",
				},
			],
			edges: [
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "derived_from",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440003",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440004",
					type: "references",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440005",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440006",
					type: "related",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440007",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440008",
					type: "next",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440009",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440010",
					type: "previous",
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		const result = await repository.getKnowledgeGraph(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual({
			nodes: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "transcript",
					title: "Transcript",
				},
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					type: "summary",
					title: "Summary",
				},
			],
			edges: [
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "derived_from",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440003",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440004",
					type: "references",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440005",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440006",
					type: "related",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440007",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440008",
					type: "next",
				},
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440009",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440010",
					type: "previous",
				},
			],
		});
	});

	it("returns empty graph when API returns no nodes or edges", async () => {
		const get = vi.fn().mockResolvedValue({ nodes: [], edges: [] });
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		const result = await repository.getKnowledgeGraph(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("returns empty graph when content id is invalid on the server", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 400));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		const result = await repository.getKnowledgeGraph(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("propagates non-400 HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		await expect(
			repository.getKnowledgeGraph("550e8400-e29b-41d4-a716-446655440000"),
		).rejects.toBeInstanceOf(ApiError);
	});

	it("uses GET /api/contents/{contentId}/graph/artifacts/{artifactId}/neighborhood", async () => {
		const get = vi.fn().mockResolvedValue({
			center: {
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				type: "summary",
				label: "Summary",
			},
			neighbors: [],
			edges: [],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		await repository.getGraphNeighborhood(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(get).toHaveBeenCalledWith(
			"/api/contents/550e8400-e29b-41d4-a716-446655440000/graph/artifacts/550e8400-e29b-41d4-a716-446655440002/neighborhood",
		);
	});

	it("maps neighborhood API DTO to graph neighborhood", async () => {
		const get = vi.fn().mockResolvedValue({
			center: {
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				type: "summary",
				label: "Summary",
			},
			neighbors: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "transcript",
					label: "Transcript",
				},
			],
			edges: [
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "derived_from",
					weight: 1,
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		const result = await repository.getGraphNeighborhood(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(result).toEqual({
			center: {
				artifactId: "550e8400-e29b-41d4-a716-446655440002",
				type: "summary",
				title: "Summary",
			},
			neighbors: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "transcript",
					title: "Transcript",
				},
			],
			edges: [
				{
					sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
					targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "derived_from",
					weight: 1,
				},
			],
		});
	});

	it("returns null when neighborhood API responds with 404", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 404));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		const result = await repository.getGraphNeighborhood(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(result).toBeNull();
	});

	it("returns null when neighborhood request has invalid ids on the server", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 400));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		const result = await repository.getGraphNeighborhood(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(result).toBeNull();
	});

	it("propagates non-400/404 neighborhood HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpGraphRepository(httpClient);

		await expect(
			repository.getGraphNeighborhood(
				"550e8400-e29b-41d4-a716-446655440000",
				"550e8400-e29b-41d4-a716-446655440002",
			),
		).rejects.toBeInstanceOf(ApiError);
	});
});
