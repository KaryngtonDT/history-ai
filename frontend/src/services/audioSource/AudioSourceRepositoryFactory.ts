import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { AudioSourceRepository } from "./AudioSourceRepository";
import { HttpAudioSourceRepository } from "./HttpAudioSourceRepository";
import { MockAudioSourceRepository } from "./MockAudioSourceRepository";

export function createAudioSourceRepository(): AudioSourceRepository {
	if (FEATURES.USE_MOCK) {
		return new MockAudioSourceRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpAudioSourceRepository(httpClient);
}
