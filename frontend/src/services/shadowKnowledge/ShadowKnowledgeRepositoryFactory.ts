import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowKnowledgeRepository } from "./HttpShadowKnowledgeRepository";
import { MockShadowKnowledgeRepository } from "./MockShadowKnowledgeRepository";
import type { ShadowKnowledgeRepository } from "./ShadowKnowledgeRepository";

export function createShadowKnowledgeRepository(): ShadowKnowledgeRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowKnowledgeRepository();
	}

	return new HttpShadowKnowledgeRepository(new HttpClient(API_BASE_URL));
}
