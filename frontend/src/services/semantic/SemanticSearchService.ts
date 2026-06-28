import type { SemanticSearchRepository } from "./SemanticSearchRepository";
import { createSemanticSearchRepository } from "./SemanticSearchRepositoryFactory";
import type { RetrievedChunk } from "./types";

const CONTENT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class SemanticSearchService {
	private readonly repository: SemanticSearchRepository;

	constructor(repository: SemanticSearchRepository) {
		this.repository = repository;
	}

	searchSemanticChunks(
		contentId: string,
		query: string,
	): Promise<RetrievedChunk[]> {
		const normalizedContentId = contentId.trim();
		const normalizedQuery = query.trim();

		if (
			normalizedContentId === "" ||
			normalizedQuery === "" ||
			!CONTENT_ID_PATTERN.test(normalizedContentId)
		) {
			return Promise.resolve([]);
		}

		return this.repository.searchSemanticChunks(
			normalizedContentId,
			normalizedQuery,
		);
	}
}

export const semanticSearchService = new SemanticSearchService(
	createSemanticSearchRepository(),
);
