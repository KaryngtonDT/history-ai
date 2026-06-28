import type { RecommendedArtifact } from "./types";

export interface RecommendationRepository {
	getArtifactRecommendations(
		contentId: string,
		artifactId: string,
	): Promise<RecommendedArtifact[]>;
}
