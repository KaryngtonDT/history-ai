import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpLibraryRepository } from "./HttpLibraryRepository";
import type { LibraryRepository } from "./LibraryRepository";
import { MockLibraryRepository } from "./MockLibraryRepository";

export function createLibraryRepository(): LibraryRepository {
	if (FEATURES.USE_MOCK) {
		return new MockLibraryRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpLibraryRepository(httpClient);
}
