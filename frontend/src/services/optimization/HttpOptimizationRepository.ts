import { videoOptimizationPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { MOCK_PREVIEW_OPTIMIZATION } from "./MockOptimizationRepository";
import type { OptimizationRepository } from "./OptimizationRepository";
import type { ExecutionOptimization } from "./types";

export class HttpOptimizationRepository implements OptimizationRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getPreviewOptimization(): Promise<ExecutionOptimization> {
		return MOCK_PREVIEW_OPTIMIZATION;
	}

	async getByVideoId(videoId: string): Promise<ExecutionOptimization> {
		return this.httpClient.get<ExecutionOptimization>(
			videoOptimizationPath(videoId),
		);
	}
}
