import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpOrchestratorRepository } from "./HttpOrchestratorRepository";
import { MockOrchestratorRepository } from "./MockOrchestratorRepository";
import type { OrchestratorRepository } from "./OrchestratorRepository";

export function createOrchestratorRepository(): OrchestratorRepository {
	if (FEATURES.USE_MOCK) {
		return new MockOrchestratorRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpOrchestratorRepository(httpClient);
}
