import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { BrowserRepository } from "./BrowserRepository";
import { HttpBrowserRepository } from "./HttpBrowserRepository";
import { MockBrowserRepository } from "./MockBrowserRepository";

export function createBrowserRepository(): BrowserRepository {
	if (FEATURES.USE_MOCK) {
		return new MockBrowserRepository();
	}

	return new HttpBrowserRepository(new HttpClient(API_BASE_URL));
}
