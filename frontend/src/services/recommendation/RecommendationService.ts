import type { RecommendationRepository } from "./RecommendationRepository";
import { createRecommendationRepository } from "./RecommendationRepositoryFactory";
import type { RecommendedArtifact } from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class RecommendationService {
	private readonly repository: RecommendationRepository;

	constructor(repository: RecommendationRepository) {
		this.repository = repository;
	}

	getArtifactRecommendations(
		contentId: string,
		artifactId: string,
	): Promise<RecommendedArtifact[]> {
		const normalizedContentId = contentId.trim();
		const normalizedArtifactId = artifactId.trim();

		if (
			normalizedContentId === "" ||
			normalizedArtifactId === "" ||
			!UUID_PATTERN.test(normalizedContentId) ||
			!UUID_PATTERN.test(normalizedArtifactId)
		) {
			return Promise.resolve([]);
		}

		return this.repository.getArtifactRecommendations(
			normalizedContentId,
			normalizedArtifactId,
		);
	}
}

export const recommendationService = new RecommendationService(
	createRecommendationRepository(),
);
