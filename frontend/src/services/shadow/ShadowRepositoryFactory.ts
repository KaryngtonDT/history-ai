import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowRepository } from "./HttpShadowRepository";
import { MockShadowRepository } from "./MockShadowRepository";
import type { ShadowRepository } from "./ShadowRepository";

export function createShadowRepository(): ShadowRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowRepository();
	}

	return new HttpShadowRepository(new HttpClient(API_BASE_URL));
}
