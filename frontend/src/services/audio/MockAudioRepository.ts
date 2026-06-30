import type { TranslationLanguage } from "@/services/translation/types";
import type { AudioRepository } from "./AudioRepository";
import type {
	GenerateAudioRequest,
	VideoAudio,
	VideoAudioSummary,
} from "./types";

const MOCK_AUDIO: VideoAudioSummary = {
	videoId: "550e8400-e29b-41d4-a716-446655440099",
	audioId: "550e8400-e29b-41d4-a716-446655440040",
	translationId: "550e8400-e29b-41d4-a716-446655440020",
	targetLanguage: "french",
	provider: "f5_tts",
	voiceId: "female_01",
	voiceDisplayName: "Female 01",
	duration: 201.5,
	format: "wav",
};

export class MockAudioRepository implements AudioRepository {
	async listAudio(videoId: string): Promise<VideoAudioSummary[]> {
		if (videoId !== MOCK_AUDIO.videoId) {
			return [];
		}

		return [MOCK_AUDIO];
	}

	async getAudio(
		videoId: string,
		language: TranslationLanguage | string,
	): Promise<VideoAudio | null> {
		if (
			videoId !== MOCK_AUDIO.videoId ||
			language !== MOCK_AUDIO.targetLanguage
		) {
			return null;
		}

		return {
			...MOCK_AUDIO,
			voiceLanguage: "french",
			voiceGender: "female",
			downloadUrl: `/api/videos/${videoId}/audio/${language}/stream`,
		};
	}

	async generateAudio(
		_videoId: string,
		_request: GenerateAudioRequest,
	): Promise<void> {
		return Promise.resolve();
	}
}
