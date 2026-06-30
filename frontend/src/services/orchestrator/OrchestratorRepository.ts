import type { PipelineRecommendation, VideoAnalysisInput } from "./types";

export interface OrchestratorRepository {
	getRecommendation(
		analysis?: VideoAnalysisInput,
	): Promise<PipelineRecommendation>;

	requestRecommendation(
		analysis: VideoAnalysisInput,
	): Promise<PipelineRecommendation>;
}
