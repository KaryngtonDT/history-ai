import type { RetrievedChunk } from "./types";

export interface SemanticSearchRepository {
	searchSemanticChunks(
		contentId: string,
		query: string,
	): Promise<RetrievedChunk[]>;
}
