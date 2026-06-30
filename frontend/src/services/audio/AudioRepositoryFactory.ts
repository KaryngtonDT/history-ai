import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { AudioRepository } from "./AudioRepository";
import { HttpAudioRepository } from "./HttpAudioRepository";
import { MockAudioRepository } from "./MockAudioRepository";

export function createAudioRepository(): AudioRepository {
	if (FEATURES.USE_MOCK) {
		return new MockAudioRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpAudioRepository(httpClient);
}
