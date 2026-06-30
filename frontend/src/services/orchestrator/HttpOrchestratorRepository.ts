import { ORCHESTRATOR_RECOMMENDATION_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { OrchestratorRepository } from "./OrchestratorRepository";
import type { PipelineRecommendation, VideoAnalysisInput } from "./types";

export class HttpOrchestratorRepository implements OrchestratorRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getRecommendation(
		analysis: VideoAnalysisInput = {},
	): Promise<PipelineRecommendation> {
		const params = new URLSearchParams();

		if (analysis.detectedLanguage) {
			params.set("detectedLanguage", analysis.detectedLanguage);
		}
		if (analysis.durationSeconds !== undefined) {
			params.set("durationSeconds", String(analysis.durationSeconds));
		}
		if (analysis.resolution) {
			params.set("resolution", analysis.resolution);
		}
		if (analysis.fps !== undefined) {
			params.set("fps", String(analysis.fps));
		}
		if (analysis.gpuAvailable !== undefined) {
			params.set("gpuAvailable", String(analysis.gpuAvailable));
		}
		if (analysis.estimatedVramGb !== undefined) {
			params.set("estimatedVramGb", String(analysis.estimatedVramGb));
		}
		if (analysis.strategy) {
			params.set("strategy", analysis.strategy);
		}

		const query = params.toString();
		const path = query
			? `${ORCHESTRATOR_RECOMMENDATION_PATH}?${query}`
			: ORCHESTRATOR_RECOMMENDATION_PATH;

		return this.httpClient.get<PipelineRecommendation>(path);
	}

	async requestRecommendation(
		analysis: VideoAnalysisInput,
	): Promise<PipelineRecommendation> {
		return this.httpClient.post<PipelineRecommendation>(
			ORCHESTRATOR_RECOMMENDATION_PATH,
			analysis,
		);
	}
}
