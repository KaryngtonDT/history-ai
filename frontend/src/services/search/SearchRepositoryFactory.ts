import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpSearchRepository } from "./HttpSearchRepository";
import { MockSearchRepository } from "./MockSearchRepository";
import type { SearchRepository } from "./SearchRepository";

export function createSearchRepository(): SearchRepository {
	if (FEATURES.USE_MOCK) {
		return new MockSearchRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpSearchRepository(httpClient);
}
