import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpRecommendationRepository } from "./HttpRecommendationRepository";
import { MockRecommendationRepository } from "./MockRecommendationRepository";
import type { RecommendationRepository } from "./RecommendationRepository";

export function createRecommendationRepository(): RecommendationRepository {
	if (FEATURES.USE_MOCK) {
		return new MockRecommendationRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpRecommendationRepository(httpClient);
}
