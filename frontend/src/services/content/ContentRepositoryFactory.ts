import { API_BASE_URL } from "@/config/api";
import { HttpClient } from "@/services/http/HttpClient";
import type { ContentRepository } from "./ContentRepository";
import { HttpContentRepository } from "./HttpContentRepository";
import { MockContentRepository } from "./MockContentRepository";

function shouldUseMock(): boolean {
	return import.meta.env.VITE_USE_MOCK === "true";
}

export function createContentRepository(): ContentRepository {
	if (shouldUseMock()) {
		return new MockContentRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpContentRepository(httpClient);
}
