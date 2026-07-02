import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowMentorRepository } from "./HttpShadowMentorRepository";
import { MockShadowMentorRepository } from "./MockShadowMentorRepository";
import type { ShadowMentorRepository } from "./ShadowMentorRepository";

export function createShadowMentorRepository(): ShadowMentorRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowMentorRepository();
	}

	return new HttpShadowMentorRepository(new HttpClient(API_BASE_URL));
}
