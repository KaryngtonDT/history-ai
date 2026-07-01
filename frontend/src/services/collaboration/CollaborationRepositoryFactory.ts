import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { CollaborationRepository } from "./CollaborationRepository";
import { HttpCollaborationRepository } from "./HttpCollaborationRepository";
import { MockCollaborationRepository } from "./MockCollaborationRepository";

export function createCollaborationRepository(): CollaborationRepository {
	if (FEATURES.USE_MOCK) {
		return new MockCollaborationRepository();
	}

	return new HttpCollaborationRepository(new HttpClient(API_BASE_URL));
}
