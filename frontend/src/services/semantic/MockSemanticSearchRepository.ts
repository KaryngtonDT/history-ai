import { artifactMocksByContentId } from "@/mock/artifact";
import type { SemanticSearchRepository } from "./SemanticSearchRepository";
import {
	buildMockSemanticResultsFromArtifacts,
	type RetrievedChunk,
} from "./types";

export class MockSemanticSearchRepository implements SemanticSearchRepository {
	async searchSemanticChunks(
		contentId: string,
		query: string,
	): Promise<RetrievedChunk[]> {
		const artifacts = artifactMocksByContentId[contentId];

		if (artifacts === undefined || artifacts.length === 0) {
			return [];
		}

		return buildMockSemanticResultsFromArtifacts(artifacts, query);
	}
}
