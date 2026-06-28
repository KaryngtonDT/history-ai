import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { GraphRepository } from "./GraphRepository";
import { HttpGraphRepository } from "./HttpGraphRepository";
import { MockGraphRepository } from "./MockGraphRepository";

export function createGraphRepository(): GraphRepository {
	if (FEATURES.USE_MOCK) {
		return new MockGraphRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpGraphRepository(httpClient);
}
