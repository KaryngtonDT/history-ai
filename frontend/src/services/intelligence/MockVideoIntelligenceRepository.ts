import type { VideoIntelligence } from "./types";

export const MOCK_PREVIEW_INTELLIGENCE: VideoIntelligence = {
	id: "550e8400-e29b-41d4-a716-446655440010",
	durationSeconds: 762,
	scene: "interview",
	audio: {
		language: "english",
		speakerCount: 2,
		backgroundNoise: "low",
		backgroundMusic: "detected",
		speechSpeed: "fast",
		confidence: 97,
	},
	visual: {
		resolution: "1920x1080",
		fps: 30,
		lighting: "good",
		lipVisibility: "excellent",
		faceCount: 2,
	},
	speech: {
		dominantEmotion: "neutral",
		averageSpeakingRate: 160,
		pauseCount: 12,
		hasOverlaps: false,
	},
	speakers: [
		{ index: 1, label: "Speaker 1" },
		{ index: 2, label: "Speaker 2" },
	],
	gpuAvailable: true,
	estimatedVramGb: 8,
};

export class MockVideoIntelligenceRepository {
	async getPreviewIntelligence(): Promise<VideoIntelligence> {
		return MOCK_PREVIEW_INTELLIGENCE;
	}

	async getByVideoId(videoId: string): Promise<VideoIntelligence> {
		return {
			...MOCK_PREVIEW_INTELLIGENCE,
			id: "550e8400-e29b-41d4-a716-446655440011",
			videoId,
		};
	}
}
