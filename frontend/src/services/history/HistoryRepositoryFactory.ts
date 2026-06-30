import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { HistoryRepository } from "./HistoryRepository";
import { HttpHistoryRepository } from "./HttpHistoryRepository";
import { MockHistoryRepository } from "./MockHistoryRepository";

export function createHistoryRepository(): HistoryRepository {
	if (FEATURES.USE_MOCK) {
		return new MockHistoryRepository();
	}

	return new HttpHistoryRepository(new HttpClient(API_BASE_URL));
}
