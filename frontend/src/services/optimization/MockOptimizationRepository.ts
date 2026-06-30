import type { ExecutionOptimization } from "./types";

export const MOCK_PREVIEW_OPTIMIZATION: ExecutionOptimization = {
	id: "550e8400-e29b-41d4-a716-446655440020",
	profile: "quality",
	summary: "Quality execution optimization for english content.",
	estimatedImpact: 5,
	explanations: [
		"Low STT confidence: beam size increased to 5.",
		"Multiple speakers detected: voice stability increased to 0.85.",
		"Excellent lighting: FFmpeg quality preset selected.",
	],
	stages: [
		{
			stage: "speech_to_text",
			parameters: [
				{ key: "beamSize", value: "5" },
				{ key: "chunkSize", value: "30" },
			],
		},
		{
			stage: "translation",
			parameters: [
				{ key: "style", value: "natural" },
				{ key: "temperature", value: "0.2" },
			],
		},
		{
			stage: "voice_clone",
			parameters: [{ key: "stability", value: "0.85" }],
		},
		{
			stage: "lip_sync",
			parameters: [{ key: "strength", value: "high" }],
		},
		{
			stage: "video_render",
			parameters: [{ key: "preset", value: "quality" }],
		},
	],
};

export class MockOptimizationRepository {
	async getPreviewOptimization(): Promise<ExecutionOptimization> {
		return MOCK_PREVIEW_OPTIMIZATION;
	}

	async getByVideoId(videoId: string): Promise<ExecutionOptimization> {
		return {
			...MOCK_PREVIEW_OPTIMIZATION,
			id: "550e8400-e29b-41d4-a716-446655440021",
			videoId,
		};
	}
}
