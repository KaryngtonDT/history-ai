import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { AgentRepository } from "./AgentRepository";
import { HttpAgentRepository } from "./HttpAgentRepository";
import { MockAgentRepository } from "./MockAgentRepository";

export function createAgentRepository(): AgentRepository {
	if (FEATURES.USE_MOCK) {
		return new MockAgentRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpAgentRepository(httpClient);
}
