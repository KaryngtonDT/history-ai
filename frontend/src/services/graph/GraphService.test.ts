import { describe, expect, it, vi } from "vitest";
import type { GraphRepository } from "./GraphRepository";
import { GraphService } from "./GraphService";
import { EMPTY_KNOWLEDGE_GRAPH } from "./types";

const graph = {
	nodes: [
		{
			artifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "transcript" as const,
			title: "Transcript",
		},
		{
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			type: "summary" as const,
			title: "Summary",
		},
	],
	edges: [
		{
			sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
			targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "derived_from" as const,
		},
	],
};

function createRepositoryMock(
	overrides: Partial<GraphRepository> = {},
): GraphRepository {
	return {
		getKnowledgeGraph: vi.fn().mockResolvedValue(EMPTY_KNOWLEDGE_GRAPH),
		...overrides,
	};
}

describe("GraphService", () => {
	it("returns knowledge graph from repository", async () => {
		const getKnowledgeGraph = vi.fn().mockResolvedValue(graph);
		const service = new GraphService(
			createRepositoryMock({ getKnowledgeGraph }),
		);

		const result = await service.getKnowledgeGraph(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(getKnowledgeGraph).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
		expect(result).toEqual(graph);
	});

	it("returns empty graph when repository returns no nodes or edges", async () => {
		const getKnowledgeGraph = vi.fn().mockResolvedValue(EMPTY_KNOWLEDGE_GRAPH);
		const service = new GraphService(
			createRepositoryMock({ getKnowledgeGraph }),
		);

		const result = await service.getKnowledgeGraph(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("returns empty graph for empty content id without calling repository", async () => {
		const getKnowledgeGraph = vi.fn();
		const service = new GraphService(
			createRepositoryMock({ getKnowledgeGraph }),
		);

		const result = await service.getKnowledgeGraph("");

		expect(getKnowledgeGraph).not.toHaveBeenCalled();
		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("returns empty graph for whitespace-only content id without calling repository", async () => {
		const getKnowledgeGraph = vi.fn();
		const service = new GraphService(
			createRepositoryMock({ getKnowledgeGraph }),
		);

		const result = await service.getKnowledgeGraph("   ");

		expect(getKnowledgeGraph).not.toHaveBeenCalled();
		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("returns empty graph for invalid content id without calling repository", async () => {
		const getKnowledgeGraph = vi.fn();
		const service = new GraphService(
			createRepositoryMock({ getKnowledgeGraph }),
		);

		const result = await service.getKnowledgeGraph("content-1");

		expect(getKnowledgeGraph).not.toHaveBeenCalled();
		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("trims content id before delegating to repository", async () => {
		const getKnowledgeGraph = vi.fn().mockResolvedValue(graph);
		const service = new GraphService(
			createRepositoryMock({ getKnowledgeGraph }),
		);

		await service.getKnowledgeGraph("  550e8400-e29b-41d4-a716-446655440000  ");

		expect(getKnowledgeGraph).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
	});

	it("supports all edge types from repository", async () => {
		const allEdgeTypesGraph = {
			nodes: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440001",
					type: "transcript" as const,
					title: "Transcript",
				},
			],
			edges: [
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
			],
		};
		const getKnowledgeGraph = vi.fn().mockResolvedValue(allEdgeTypesGraph);
		const service = new GraphService(
			createRepositoryMock({ getKnowledgeGraph }),
		);

		const result = await service.getKnowledgeGraph(
			"550e8400-e29b-41d4-a716-446655440000",
		);

		expect(result).toEqual(allEdgeTypesGraph);
	});
});
