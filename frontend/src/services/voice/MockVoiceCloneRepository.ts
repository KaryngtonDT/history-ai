import type {
	GenerateVoiceCloneRequest,
	VideoVoiceClone,
	VideoVoiceCloneSummary,
} from "./types";
import type { VoiceCloneRepository } from "./VoiceCloneRepository";

const MOCK_CLONE: VideoVoiceClone = {
	videoId: "550e8400-e29b-41d4-a716-446655440099",
	artifactId: "550e8400-e29b-41d4-a716-446655440050",
	sourceAudioId: "550e8400-e29b-41d4-a716-446655440030",
	clonedAudioId: "550e8400-e29b-41d4-a716-446655440060",
	targetLanguage: "french",
	provider: "openvoice",
	sourceLanguage: "english",
	duration: 201.5,
	sampleRate: 44100,
	originalAudioUrl:
		"/api/videos/550e8400-e29b-41d4-a716-446655440099/audio/french/stream",
	clonedAudioUrl:
		"/api/videos/550e8400-e29b-41d4-a716-446655440099/voice-clone/french/stream",
};

export class MockVoiceCloneRepository implements VoiceCloneRepository {
	async listVoiceClones(videoId: string): Promise<VideoVoiceCloneSummary[]> {
		if (videoId !== MOCK_CLONE.videoId) {
			return [];
		}

		return [MOCK_CLONE];
	}

	async getVoiceClone(
		videoId: string,
		language: string,
	): Promise<VideoVoiceClone | null> {
		if (
			videoId !== MOCK_CLONE.videoId ||
			language !== MOCK_CLONE.targetLanguage
		) {
			return null;
		}

		return MOCK_CLONE;
	}

	async generateVoiceClone(
		_videoId: string,
		_request: GenerateVoiceCloneRequest,
	): Promise<void> {
		return Promise.resolve();
	}
}
