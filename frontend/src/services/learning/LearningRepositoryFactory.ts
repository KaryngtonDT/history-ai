import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpLearningRepository } from "./HttpLearningRepository";
import type { LearningRepository } from "./LearningRepository";
import { MockLearningRepository } from "./MockLearningRepository";

export function createLearningRepository(): LearningRepository {
	if (FEATURES.USE_MOCK) {
		return new MockLearningRepository();
	}

	return new HttpLearningRepository(new HttpClient(API_BASE_URL));
}
