import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpVoiceCloneRepository } from "./HttpVoiceCloneRepository";
import { MockVoiceCloneRepository } from "./MockVoiceCloneRepository";
import type { VoiceCloneRepository } from "./VoiceCloneRepository";

export function createVoiceCloneRepository(): VoiceCloneRepository {
	if (FEATURES.USE_MOCK) {
		return new MockVoiceCloneRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpVoiceCloneRepository(httpClient);
}
