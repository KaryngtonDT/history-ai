import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowIdentityRepository } from "./HttpShadowIdentityRepository";
import { MockShadowIdentityRepository } from "./MockShadowIdentityRepository";
import type { ShadowIdentityRepository } from "./ShadowIdentityRepository";

export function createShadowIdentityRepository(): ShadowIdentityRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowIdentityRepository();
	}

	return new HttpShadowIdentityRepository(new HttpClient(API_BASE_URL));
}
