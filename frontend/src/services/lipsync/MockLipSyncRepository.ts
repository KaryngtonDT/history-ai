import type { TranslationLanguage } from "@/services/translation/types";
import type { LipSyncRepository } from "./LipSyncRepository";
import type {
	GenerateLipSyncRequest,
	VideoLipSync,
	VideoLipSyncSummary,
} from "./types";

const MOCK_LIP_SYNC: VideoLipSync = {
	videoId: "550e8400-e29b-41d4-a716-446655440099",
	artifactId: "550e8400-e29b-41d4-a716-446655440080",
	clonedAudioId: "550e8400-e29b-41d4-a716-446655440060",
	targetLanguage: "french" as TranslationLanguage,
	provider: "latentsync",
	synchronizedVideoId: "550e8400-e29b-41d4-a716-446655440070",
	duration: 42,
	originalVideoUrl: "/api/videos/550e8400-e29b-41d4-a716-446655440099/stream",
	syncedVideoUrl:
		"/api/videos/550e8400-e29b-41d4-a716-446655440099/lip-sync/french/stream",
};

export class MockLipSyncRepository implements LipSyncRepository {
	async listLipSyncs(videoId: string): Promise<VideoLipSyncSummary[]> {
		if (videoId !== MOCK_LIP_SYNC.videoId) {
			return [];
		}

		return [MOCK_LIP_SYNC];
	}

	async getLipSync(
		videoId: string,
		language: string,
	): Promise<VideoLipSync | null> {
		if (
			videoId !== MOCK_LIP_SYNC.videoId ||
			language !== MOCK_LIP_SYNC.targetLanguage
		) {
			return null;
		}

		return MOCK_LIP_SYNC;
	}

	async generateLipSync(
		_videoId: string,
		_request: GenerateLipSyncRequest,
	): Promise<void> {
		return Promise.resolve();
	}
}
