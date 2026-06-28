import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpRelationRepository } from "./HttpRelationRepository";
import { MockRelationRepository } from "./MockRelationRepository";
import type { RelationRepository } from "./RelationRepository";

export function createRelationRepository(): RelationRepository {
	if (FEATURES.USE_MOCK) {
		return new MockRelationRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpRelationRepository(httpClient);
}
