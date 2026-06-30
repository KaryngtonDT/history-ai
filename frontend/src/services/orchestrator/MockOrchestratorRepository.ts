import type { OrchestratorRepository } from "./OrchestratorRepository";
import type { PipelineRecommendation, VideoAnalysisInput } from "./types";

const MOCK_RECOMMENDATION: PipelineRecommendation = {
	id: "550e8400-e29b-41d4-a716-446655440099",
	strategy: "balanced",
	explanation:
		"Balanced pipeline for English content targeting French and German translations.",
	estimatedDurationSeconds: 240,
	estimatedQuality: 4,
	estimatedVramGb: 8,
	stages: [
		{ stage: "speech_to_text", providerId: "faster_whisper" },
		{ stage: "translation", providerId: "ollama" },
		{ stage: "text_to_speech", providerId: "f5_tts" },
		{ stage: "voice_clone", providerId: "openvoice" },
		{ stage: "lip_sync", providerId: "latentsync" },
		{ stage: "video_render", providerId: "ffmpeg" },
	],
};

export class MockOrchestratorRepository implements OrchestratorRepository {
	async getRecommendation(
		_analysis?: VideoAnalysisInput,
	): Promise<PipelineRecommendation> {
		return MOCK_RECOMMENDATION;
	}

	async requestRecommendation(
		_analysis: VideoAnalysisInput,
	): Promise<PipelineRecommendation> {
		return MOCK_RECOMMENDATION;
	}
}
