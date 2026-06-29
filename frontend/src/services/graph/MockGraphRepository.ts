import { artifactMocksByContentId } from "@/mock/artifact";
import { mockConversationDocumentsById } from "@/mock/conversationGraph";
import type { GraphRepository } from "./GraphRepository";
import {
	buildGraphNeighborhoodFromGraph,
	buildKnowledgeGraphFromArtifacts,
	EMPTY_KNOWLEDGE_GRAPH,
	type GraphNeighborhood,
	type KnowledgeGraph,
} from "./types";

export class MockGraphRepository implements GraphRepository {
	async getKnowledgeGraph(contentId: string): Promise<KnowledgeGraph> {
		const artifacts = artifactMocksByContentId[contentId];

		if (artifacts === undefined || artifacts.length === 0) {
			return EMPTY_KNOWLEDGE_GRAPH;
		}

		return buildKnowledgeGraphFromArtifacts(artifacts);
	}

	async getGraphNeighborhood(
		contentId: string,
		artifactId: string,
	): Promise<GraphNeighborhood | null> {
		const graph = await this.getKnowledgeGraph(contentId);

		return buildGraphNeighborhoodFromGraph(graph, artifactId);
	}

	async getConversationGraph(conversationId: string): Promise<KnowledgeGraph> {
		const contentIds = mockConversationDocumentsById[conversationId];

		if (contentIds === undefined || contentIds.length === 0) {
			return EMPTY_KNOWLEDGE_GRAPH;
		}

		const artifacts = contentIds.flatMap(
			(contentId) => artifactMocksByContentId[contentId] ?? [],
		);

		if (artifacts.length === 0) {
			return EMPTY_KNOWLEDGE_GRAPH;
		}

		return buildKnowledgeGraphFromArtifacts(artifacts);
	}
}
