import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpVideoIntelligenceRepository } from "./HttpVideoIntelligenceRepository";
import { MockVideoIntelligenceRepository } from "./MockVideoIntelligenceRepository";
import type { VideoIntelligenceRepository } from "./VideoIntelligenceRepository";

export function createVideoIntelligenceRepository(): VideoIntelligenceRepository {
	if (FEATURES.USE_MOCK) {
		return new MockVideoIntelligenceRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpVideoIntelligenceRepository(httpClient);
}
