import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpQualityRepository } from "./HttpQualityRepository";
import { MockQualityRepository } from "./MockQualityRepository";
import type { QualityRepository } from "./QualityRepository";

export function createQualityRepository(): QualityRepository {
	if (FEATURES.USE_MOCK) {
		return new MockQualityRepository();
	}

	return new HttpQualityRepository(new HttpClient(API_BASE_URL));
}
