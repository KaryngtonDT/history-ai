import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpLipSyncRepository } from "./HttpLipSyncRepository";
import type { LipSyncRepository } from "./LipSyncRepository";
import { MockLipSyncRepository } from "./MockLipSyncRepository";

export function createLipSyncRepository(): LipSyncRepository {
	if (FEATURES.USE_MOCK) {
		return new MockLipSyncRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpLipSyncRepository(httpClient);
}
