import type { PipelineConfiguration, PipelineStage } from "./types";

export interface PipelineRepository {
	loadConfiguration(): Promise<PipelineConfiguration>;
	saveConfiguration(stages: PipelineStage[]): Promise<PipelineConfiguration>;
	resetConfiguration(): Promise<PipelineConfiguration>;
}
