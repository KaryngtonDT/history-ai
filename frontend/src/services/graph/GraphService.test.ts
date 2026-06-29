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
		getGraphNeighborhood: vi.fn().mockResolvedValue(null),
		getConversationGraph: vi.fn().mockResolvedValue(EMPTY_KNOWLEDGE_GRAPH),
		...overrides,
	};
}

const neighborhood = {
	center: {
		artifactId: "550e8400-e29b-41d4-a716-446655440002",
		type: "summary" as const,
		title: "Summary",
	},
	neighbors: [
		{
			artifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "transcript" as const,
			title: "Transcript",
		},
	],
	edges: [
		{
			sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
			targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "derived_from" as const,
			weight: 1,
		},
	],
};

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

	it("returns neighborhood from repository", async () => {
		const getGraphNeighborhood = vi.fn().mockResolvedValue(neighborhood);
		const service = new GraphService(
			createRepositoryMock({ getGraphNeighborhood }),
		);

		const result = await service.getGraphNeighborhood(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(getGraphNeighborhood).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);
		expect(result).toEqual(neighborhood);
	});

	it("returns null when repository returns null", async () => {
		const getGraphNeighborhood = vi.fn().mockResolvedValue(null);
		const service = new GraphService(
			createRepositoryMock({ getGraphNeighborhood }),
		);

		const result = await service.getGraphNeighborhood(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(result).toBeNull();
	});

	it("returns null for invalid content id without calling repository", async () => {
		const getGraphNeighborhood = vi.fn();
		const service = new GraphService(
			createRepositoryMock({ getGraphNeighborhood }),
		);

		const result = await service.getGraphNeighborhood(
			"content-1",
			"550e8400-e29b-41d4-a716-446655440002",
		);

		expect(getGraphNeighborhood).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("returns null for invalid artifact id without calling repository", async () => {
		const getGraphNeighborhood = vi.fn();
		const service = new GraphService(
			createRepositoryMock({ getGraphNeighborhood }),
		);

		const result = await service.getGraphNeighborhood(
			"550e8400-e29b-41d4-a716-446655440000",
			"artifact-summary-1",
		);

		expect(getGraphNeighborhood).not.toHaveBeenCalled();
		expect(result).toBeNull();
	});

	it("trims ids before delegating neighborhood lookup to repository", async () => {
		const getGraphNeighborhood = vi.fn().mockResolvedValue(neighborhood);
		const service = new GraphService(
			createRepositoryMock({ getGraphNeighborhood }),
		);

		await service.getGraphNeighborhood(
			"  550e8400-e29b-41d4-a716-446655440000  ",
			"  550e8400-e29b-41d4-a716-446655440002  ",
		);

		expect(getGraphNeighborhood).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);
	});
});

describe("GraphService conversation graph", () => {
	it("returns conversation graph from repository", async () => {
		const getConversationGraph = vi.fn().mockResolvedValue(graph);
		const service = new GraphService(
			createRepositoryMock({ getConversationGraph }),
		);

		const result = await service.getConversationGraph(
			"550e8400-e29b-41d4-a716-446655440001",
		);

		expect(getConversationGraph).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440001",
		);
		expect(result).toEqual(graph);
	});

	it("returns empty graph when repository returns no nodes or edges", async () => {
		const getConversationGraph = vi
			.fn()
			.mockResolvedValue(EMPTY_KNOWLEDGE_GRAPH);
		const service = new GraphService(
			createRepositoryMock({ getConversationGraph }),
		);

		const result = await service.getConversationGraph(
			"550e8400-e29b-41d4-a716-446655440001",
		);

		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("returns empty graph for invalid conversation id without calling repository", async () => {
		const getConversationGraph = vi.fn();
		const service = new GraphService(
			createRepositoryMock({ getConversationGraph }),
		);

		const result = await service.getConversationGraph("conversation-1");

		expect(getConversationGraph).not.toHaveBeenCalled();
		expect(result).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("trims conversation id before delegating to repository", async () => {
		const getConversationGraph = vi.fn().mockResolvedValue(graph);
		const service = new GraphService(
			createRepositoryMock({ getConversationGraph }),
		);

		await service.getConversationGraph(
			"  550e8400-e29b-41d4-a716-446655440001  ",
		);

		expect(getConversationGraph).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440001",
		);
	});
});
