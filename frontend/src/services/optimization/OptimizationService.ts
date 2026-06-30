import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type { OptimizationRepository } from "./OptimizationRepository";
import { createOptimizationRepository } from "./OptimizationRepositoryFactory";
import type { ExecutionOptimization, OptimizationStage } from "./types";
import {
	OPTIMIZATION_PARAMETER_LABELS,
	OPTIMIZATION_PROFILE_LABELS,
	OPTIMIZATION_STAGE_LABELS,
} from "./types";

export class OptimizationService {
	private readonly repository: OptimizationRepository;

	constructor(repository: OptimizationRepository) {
		this.repository = repository;
	}

	loadPreviewOptimization(): Promise<ExecutionOptimization> {
		return this.repository.getPreviewOptimization();
	}

	loadByVideoId(videoId: string): Promise<ExecutionOptimization> {
		return this.repository.getByVideoId(videoId);
	}

	formatProfile(profile: string): string {
		return OPTIMIZATION_PROFILE_LABELS[profile] ?? profile;
	}

	formatStageLabel(stage: string): string {
		return OPTIMIZATION_STAGE_LABELS[stage] ?? stage;
	}

	formatParameterLabel(key: string): string {
		return OPTIMIZATION_PARAMETER_LABELS[key] ?? key;
	}

	formatImpactStars(impact: number): string {
		return orchestratorService.formatQualityStars(impact);
	}

	primaryParameters(stage: OptimizationStage): OptimizationStage["parameters"] {
		return stage.parameters.slice(0, 2);
	}
}

export const optimizationService = new OptimizationService(
	createOptimizationRepository(),
);
