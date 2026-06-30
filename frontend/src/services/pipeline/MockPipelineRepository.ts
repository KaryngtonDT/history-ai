import type { PipelineRepository } from "./PipelineRepository";
import type { PipelineConfiguration, PipelineStage } from "./types";

const MOCK_CONFIGURATION: PipelineConfiguration = {
	id: "550e8400-e29b-41d4-a716-446655440010",
	version: 1,
	createdAt: "2026-06-26T10:00:00+00:00",
	updatedAt: "2026-06-26T10:00:00+00:00",
	stages: [
		{ stage: "speech_to_text", providerId: "faster_whisper" },
		{ stage: "translation", providerId: "ollama" },
		{ stage: "text_to_speech", providerId: "f5_tts" },
		{ stage: "voice_clone", providerId: "openvoice" },
		{ stage: "lip_sync", providerId: "latentsync" },
		{ stage: "video_render", providerId: "ffmpeg" },
	],
};

export class MockPipelineRepository implements PipelineRepository {
	private configuration = MOCK_CONFIGURATION;

	async loadConfiguration(): Promise<PipelineConfiguration> {
		return this.configuration;
	}

	async saveConfiguration(
		stages: PipelineStage[],
	): Promise<PipelineConfiguration> {
		this.configuration = {
			...this.configuration,
			version: this.configuration.version + 1,
			stages,
		};

		return this.configuration;
	}

	async resetConfiguration(): Promise<PipelineConfiguration> {
		this.configuration = MOCK_CONFIGURATION;

		return this.configuration;
	}
}
