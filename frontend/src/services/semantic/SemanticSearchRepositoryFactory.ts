import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpSemanticSearchRepository } from "./HttpSemanticSearchRepository";
import { MockSemanticSearchRepository } from "./MockSemanticSearchRepository";
import type { SemanticSearchRepository } from "./SemanticSearchRepository";

export function createSemanticSearchRepository(): SemanticSearchRepository {
	if (FEATURES.USE_MOCK) {
		return new MockSemanticSearchRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpSemanticSearchRepository(httpClient);
}
