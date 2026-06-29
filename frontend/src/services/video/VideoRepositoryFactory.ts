import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpVideoRepository } from "./HttpVideoRepository";
import { MockVideoRepository } from "./MockVideoRepository";
import type { VideoRepository } from "./VideoRepository";

export function createVideoRepository(): VideoRepository {
	if (FEATURES.USE_MOCK) {
		return new MockVideoRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpVideoRepository(httpClient);
}
