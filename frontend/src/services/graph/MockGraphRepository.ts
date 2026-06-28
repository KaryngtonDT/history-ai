import { artifactMocksByContentId } from "@/mock/artifact";
import type { GraphRepository } from "./GraphRepository";
import {
	buildKnowledgeGraphFromArtifacts,
	EMPTY_KNOWLEDGE_GRAPH,
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
}
