import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import type { ArtifactRepository } from "./ArtifactRepository";
import { HttpArtifactRepository } from "./HttpArtifactRepository";
import { MockArtifactRepository } from "./MockArtifactRepository";

export function createArtifactRepository(): ArtifactRepository {
	if (FEATURES.USE_MOCK) {
		return new MockArtifactRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpArtifactRepository(httpClient);
}
