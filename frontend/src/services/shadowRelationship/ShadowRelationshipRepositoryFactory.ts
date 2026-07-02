import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowRelationshipRepository } from "./HttpShadowRelationshipRepository";
import { MockShadowRelationshipRepository } from "./MockShadowRelationshipRepository";
import type { ShadowRelationshipRepository } from "./ShadowRelationshipRepository";

export function createShadowRelationshipRepository(): ShadowRelationshipRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowRelationshipRepository();
	}

	return new HttpShadowRelationshipRepository(new HttpClient(API_BASE_URL));
}
