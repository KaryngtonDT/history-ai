import type { GraphRepository } from "./GraphRepository";
import { createGraphRepository } from "./GraphRepositoryFactory";
import {
	EMPTY_KNOWLEDGE_GRAPH,
	type GraphNeighborhood,
	type KnowledgeGraph,
} from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class GraphService {
	private readonly repository: GraphRepository;

	constructor(repository: GraphRepository) {
		this.repository = repository;
	}

	getKnowledgeGraph(contentId: string): Promise<KnowledgeGraph> {
		const normalized = contentId.trim();

		if (normalized === "" || !UUID_PATTERN.test(normalized)) {
			return Promise.resolve(EMPTY_KNOWLEDGE_GRAPH);
		}

		return this.repository.getKnowledgeGraph(normalized);
	}

	getGraphNeighborhood(
		contentId: string,
		artifactId: string,
	): Promise<GraphNeighborhood | null> {
		const normalizedContentId = contentId.trim();
		const normalizedArtifactId = artifactId.trim();

		if (
			normalizedContentId === "" ||
			normalizedArtifactId === "" ||
			!UUID_PATTERN.test(normalizedContentId) ||
			!UUID_PATTERN.test(normalizedArtifactId)
		) {
			return Promise.resolve(null);
		}

		return this.repository.getGraphNeighborhood(
			normalizedContentId,
			normalizedArtifactId,
		);
	}

	getConversationGraph(conversationId: string): Promise<KnowledgeGraph> {
		const normalized = conversationId.trim();

		if (normalized === "" || !UUID_PATTERN.test(normalized)) {
			return Promise.resolve(EMPTY_KNOWLEDGE_GRAPH);
		}

		return this.repository.getConversationGraph(normalized);
	}
}

export const graphService = new GraphService(createGraphRepository());
