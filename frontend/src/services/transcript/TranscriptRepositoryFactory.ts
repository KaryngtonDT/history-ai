import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpTranscriptRepository } from "./HttpTranscriptRepository";
import { MockTranscriptRepository } from "./MockTranscriptRepository";
import type { TranscriptRepository } from "./TranscriptRepository";

export function createTranscriptRepository(): TranscriptRepository {
	if (FEATURES.USE_MOCK) {
		return new MockTranscriptRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpTranscriptRepository(httpClient);
}
