import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { AIEngineRepository } from "./AIEngineRepository";
import { HttpAIEngineRepository } from "./HttpAIEngineRepository";
import { MockAIEngineRepository } from "./MockAIEngineRepository";

export function createAIEngineRepository(): AIEngineRepository {
	if (FEATURES.USE_MOCK) {
		return new MockAIEngineRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpAIEngineRepository(httpClient);
}
