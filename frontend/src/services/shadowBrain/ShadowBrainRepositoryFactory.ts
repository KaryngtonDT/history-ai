import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowBrainRepository } from "./HttpShadowBrainRepository";
import { MockShadowBrainRepository } from "./MockShadowBrainRepository";
import type { ShadowBrainRepository } from "./ShadowBrainRepository";

export function createShadowBrainRepository(): ShadowBrainRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowBrainRepository();
	}

	return new HttpShadowBrainRepository(new HttpClient(API_BASE_URL));
}
