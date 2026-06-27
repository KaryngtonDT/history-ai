import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpMapRepository } from "./HttpMapRepository";
import type { MapRepository } from "./MapRepository";
import { MockMapRepository } from "./MockMapRepository";

export function createMapRepository(): MapRepository {
	if (FEATURES.USE_MOCK) {
		return new MockMapRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpMapRepository(httpClient);
}
