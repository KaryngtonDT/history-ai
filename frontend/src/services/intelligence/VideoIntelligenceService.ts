import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import type { VideoIntelligence } from "./types";
import type { VideoIntelligenceRepository } from "./VideoIntelligenceRepository";
import { createVideoIntelligenceRepository } from "./VideoIntelligenceRepositoryFactory";

export class VideoIntelligenceService {
	private readonly repository: VideoIntelligenceRepository;

	constructor(repository: VideoIntelligenceRepository) {
		this.repository = repository;
	}

	loadPreviewIntelligence(): Promise<VideoIntelligence> {
		return this.repository.getPreviewIntelligence();
	}

	loadByVideoId(videoId: string): Promise<VideoIntelligence> {
		return this.repository.getByVideoId(videoId);
	}

	formatDuration(seconds: number): string {
		const minutes = Math.floor(seconds / 60);
		const remainingSeconds = Math.round(seconds % 60);
		return `${minutes}m ${remainingSeconds}s`;
	}

	formatConfidence(confidence: number): string {
		return `${confidence}%`;
	}

	formatQualityStars(quality: number): string {
		return orchestratorService.formatQualityStars(quality);
	}

	formatRecommendationSummary(
		recommendation: PipelineRecommendation | null,
	): string {
		if (!recommendation) {
			return "";
		}

		return recommendation.explanation;
	}
}

export const videoIntelligenceService = new VideoIntelligenceService(
	createVideoIntelligenceRepository(),
);
