import { artifactMocksByContentId } from "@/mock/artifact";
import type { RecommendationRepository } from "./RecommendationRepository";
import {
	buildRecommendationsFromArtifacts,
	type RecommendedArtifact,
} from "./types";

export class MockRecommendationRepository implements RecommendationRepository {
	async getArtifactRecommendations(
		contentId: string,
		artifactId: string,
	): Promise<RecommendedArtifact[]> {
		const artifacts = artifactMocksByContentId[contentId];

		if (artifacts === undefined || artifacts.length === 0) {
			return [];
		}

		return buildRecommendationsFromArtifacts(artifacts, artifactId);
	}
}
