import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpYouTubeSourceRepository } from "./HttpYouTubeSourceRepository";
import { MockYouTubeSourceRepository } from "./MockYouTubeSourceRepository";
import type { YouTubeSourceRepository } from "./YouTubeSourceRepository";

export function createYouTubeSourceRepository(): YouTubeSourceRepository {
	if (FEATURES.USE_MOCK) {
		return new MockYouTubeSourceRepository();
	}

	return new HttpYouTubeSourceRepository(new HttpClient(API_BASE_URL));
}
