import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpTimelineRepository } from "./HttpTimelineRepository";
import { MockTimelineRepository } from "./MockTimelineRepository";
import type { TimelineRepository } from "./TimelineRepository";

export function createTimelineRepository(): TimelineRepository {
	if (FEATURES.USE_MOCK) {
		return new MockTimelineRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpTimelineRepository(httpClient);
}
