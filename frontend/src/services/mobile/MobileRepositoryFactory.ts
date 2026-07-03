import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpMobileRepository } from "./HttpMobileRepository";
import type { MobileRepository } from "./MobileRepository";
import { MockMobileRepository } from "./MockMobileRepository";

export function createMobileRepository(): MobileRepository {
	if (FEATURES.USE_MOCK) {
		return new MockMobileRepository();
	}

	return new HttpMobileRepository(new HttpClient(API_BASE_URL));
}
