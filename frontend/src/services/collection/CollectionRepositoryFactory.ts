import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { CollectionRepository } from "./CollectionRepository";
import { HttpCollectionRepository } from "./HttpCollectionRepository";
import { MockCollectionRepository } from "./MockCollectionRepository";

export function createCollectionRepository(): CollectionRepository {
	if (FEATURES.USE_MOCK) {
		return new MockCollectionRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpCollectionRepository(httpClient);
}
