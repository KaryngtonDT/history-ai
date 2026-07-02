import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpShadowVoiceRepository } from "./HttpShadowVoiceRepository";
import { MockShadowVoiceRepository } from "./MockShadowVoiceRepository";
import type { ShadowVoiceRepository } from "./ShadowVoiceRepository";

export function createShadowVoiceRepository(): ShadowVoiceRepository {
	if (FEATURES.USE_MOCK) {
		return new MockShadowVoiceRepository();
	}

	return new HttpShadowVoiceRepository(new HttpClient(API_BASE_URL));
}
