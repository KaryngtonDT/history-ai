import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { ContentRepository } from "./ContentRepository";
import { HttpContentRepository } from "./HttpContentRepository";
import { MockContentRepository } from "./MockContentRepository";

export function createContentRepository(): ContentRepository {
	if (FEATURES.USE_MOCK) {
		return new MockContentRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpContentRepository(httpClient);
}
