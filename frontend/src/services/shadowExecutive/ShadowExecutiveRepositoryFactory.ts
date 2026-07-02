import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowExecutiveRepository } from "./HttpShadowExecutiveRepository";
import { MockShadowExecutiveRepository } from "./MockShadowExecutiveRepository";
import type { ShadowExecutiveRepository } from "./ShadowExecutiveRepository";

export function createShadowExecutiveRepository(): ShadowExecutiveRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowExecutiveRepository();
	}

	return new HttpShadowExecutiveRepository(new HttpClient(API_BASE_URL));
}
