import type { QualityReport } from "./types";

export const MOCK_PREVIEW_QUALITY: QualityReport = {
	id: "550e8400-e29b-41d4-a716-446655440040",
	overallScore: 94,
	recommendation: "ready",
	metrics: [
		{ category: "audio", score: 98, explanation: "Clean audio track." },
		{
			category: "translation",
			score: 95,
			explanation: "Strong translation confidence.",
		},
		{
			category: "voice_clone",
			score: 93,
			explanation: "Natural voice clone output.",
		},
		{
			category: "lip_sync",
			score: 89,
			explanation: "Good lip sync alignment.",
		},
		{
			category: "rendering",
			score: 100,
			explanation: "High render quality preset applied.",
		},
	],
	explanations: ["Overall quality is excellent."],
};

export class MockQualityRepository {
	async getPreviewQuality(): Promise<QualityReport> {
		return MOCK_PREVIEW_QUALITY;
	}

	async getByVideoId(videoId: string): Promise<QualityReport> {
		return {
			...MOCK_PREVIEW_QUALITY,
			id: "550e8400-e29b-41d4-a716-446655440041",
			videoId,
		};
	}
}
