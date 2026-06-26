import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpProcessingRepository } from "./HttpProcessingRepository";
import { MockProcessingRepository } from "./MockProcessingRepository";
import type { ProcessingRepository } from "./ProcessingRepository";

export function createProcessingRepository(): ProcessingRepository {
	if (FEATURES.USE_MOCK) {
		return new MockProcessingRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpProcessingRepository(httpClient);
}
