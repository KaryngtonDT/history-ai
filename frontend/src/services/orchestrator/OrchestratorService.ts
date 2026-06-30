import type { OrchestratorRepository } from "./OrchestratorRepository";
import { createOrchestratorRepository } from "./OrchestratorRepositoryFactory";
import type {
	PipelineRecommendation,
	ProcessingMode,
	VideoAnalysisInput,
} from "./types";

export class OrchestratorService {
	private readonly repository: OrchestratorRepository;

	constructor(repository: OrchestratorRepository) {
		this.repository = repository;
	}

	loadRecommendation(
		analysis?: VideoAnalysisInput,
	): Promise<PipelineRecommendation> {
		return this.repository.getRecommendation(analysis);
	}

	requestRecommendation(
		analysis: VideoAnalysisInput,
	): Promise<PipelineRecommendation> {
		return this.repository.requestRecommendation(analysis);
	}

	formatEstimatedDuration(seconds: number): string {
		const minutes = Math.max(1, Math.round(seconds / 60));
		return `${minutes} min`;
	}

	formatQualityStars(quality: number): string {
		return "★".repeat(Math.min(5, Math.max(1, quality)));
	}

	isAutomaticMode(mode: ProcessingMode): boolean {
		return mode === "automatic";
	}
}

export const orchestratorService = new OrchestratorService(
	createOrchestratorRepository(),
);
