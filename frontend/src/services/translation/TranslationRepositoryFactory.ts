import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpTranslationRepository } from "./HttpTranslationRepository";
import { MockTranslationRepository } from "./MockTranslationRepository";
import type { TranslationRepository } from "./TranslationRepository";

export function createTranslationRepository(): TranslationRepository {
	if (FEATURES.USE_MOCK) {
		return new MockTranslationRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpTranslationRepository(httpClient);
}
