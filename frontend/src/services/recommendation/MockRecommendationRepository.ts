import { artifactMocksByContentId } from "@/mock/artifact";
import type { RecommendationRepository } from "./RecommendationRepository";
import {
	buildRecommendationsFromArtifacts,
	type RecommendationReason,
	type RecommendedArtifact,
} from "./types";

const MOCK_SCORE_BY_REASON: Record<RecommendationReason, number> = {
	derived_from: 100,
	references: 80,
	related: 60,
	next: 40,
	previous: 40,
};

export class MockRecommendationRepository implements RecommendationRepository {
	async getArtifactRecommendations(
		contentId: string,
		artifactId: string,
	): Promise<RecommendedArtifact[]> {
		const artifacts = artifactMocksByContentId[contentId];

		if (artifacts === undefined || artifacts.length === 0) {
			return [];
		}

		return buildRecommendationsFromArtifacts(artifacts, artifactId).map(
			(recommendation) => ({
				...recommendation,
				score: MOCK_SCORE_BY_REASON[recommendation.reason],
			}),
		);
	}
}
