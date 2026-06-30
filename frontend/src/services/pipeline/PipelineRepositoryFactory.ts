import { API_BASE_URL } from "@/config/api";
import { FEATURES } from "@/config/features";
import { HttpClient } from "@/services/http/HttpClient";
import { HttpPipelineRepository } from "./HttpPipelineRepository";
import { MockPipelineRepository } from "./MockPipelineRepository";
import type { PipelineRepository } from "./PipelineRepository";

export function createPipelineRepository(): PipelineRepository {
	if (FEATURES.USE_MOCK) {
		return new MockPipelineRepository();
	}

	const httpClient = new HttpClient(API_BASE_URL);
	return new HttpPipelineRepository(httpClient);
}
