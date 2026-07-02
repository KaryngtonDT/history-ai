import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowMemoryRepository } from "./HttpShadowMemoryRepository";
import { MockShadowMemoryRepository } from "./MockShadowMemoryRepository";
import type { ShadowMemoryRepository } from "./ShadowMemoryRepository";

export function createShadowMemoryRepository(): ShadowMemoryRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowMemoryRepository();
	}

	return new HttpShadowMemoryRepository(new HttpClient(API_BASE_URL));
}
