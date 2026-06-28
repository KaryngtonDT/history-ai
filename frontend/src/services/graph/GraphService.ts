import type { GraphRepository } from "./GraphRepository";
import { createGraphRepository } from "./GraphRepositoryFactory";
import { EMPTY_KNOWLEDGE_GRAPH, type KnowledgeGraph } from "./types";

const CONTENT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class GraphService {
	private readonly repository: GraphRepository;

	constructor(repository: GraphRepository) {
		this.repository = repository;
	}

	getKnowledgeGraph(contentId: string): Promise<KnowledgeGraph> {
		const normalized = contentId.trim();

		if (normalized === "" || !CONTENT_ID_PATTERN.test(normalized)) {
			return Promise.resolve(EMPTY_KNOWLEDGE_GRAPH);
		}

		return this.repository.getKnowledgeGraph(normalized);
	}
}

export const graphService = new GraphService(createGraphRepository());
