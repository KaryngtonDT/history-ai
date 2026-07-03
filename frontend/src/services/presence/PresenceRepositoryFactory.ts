import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpPresenceRepository } from "./HttpPresenceRepository";
import { MockPresenceRepository } from "./MockPresenceRepository";
import type { PresenceRepository } from "./PresenceRepository";

export function createPresenceRepository(): PresenceRepository {
	if (FEATURES.USE_MOCK) {
		return new MockPresenceRepository();
	}

	return new HttpPresenceRepository(new HttpClient(API_BASE_URL));
}
