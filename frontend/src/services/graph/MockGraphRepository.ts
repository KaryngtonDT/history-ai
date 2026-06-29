import { artifactMocksByContentId } from "@/mock/artifact";
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
}
