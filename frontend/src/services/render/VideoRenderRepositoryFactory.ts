import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpVideoRenderRepository } from "./HttpVideoRenderRepository";
import { MockVideoRenderRepository } from "./MockVideoRenderRepository";
import type { VideoRenderRepository } from "./VideoRenderRepository";

export function createVideoRenderRepository(): VideoRenderRepository {
	if (FEATURES.USE_MOCK) {
		return new MockVideoRenderRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpVideoRenderRepository(httpClient);
}
