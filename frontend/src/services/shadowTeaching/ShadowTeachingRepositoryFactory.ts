import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowTeachingRepository } from "./HttpShadowTeachingRepository";
import { MockShadowTeachingRepository } from "./MockShadowTeachingRepository";
import type { ShadowTeachingRepository } from "./ShadowTeachingRepository";

export function createShadowTeachingRepository(): ShadowTeachingRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowTeachingRepository();
	}

	return new HttpShadowTeachingRepository(new HttpClient(API_BASE_URL));
}
