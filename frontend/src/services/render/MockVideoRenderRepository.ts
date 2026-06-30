import type {
	GenerateVideoRenderRequest,
	VideoRender,
	VideoRenderSummary,
} from "./types";
import type { VideoRenderRepository } from "./VideoRenderRepository";

const MOCK_RENDER: VideoRender = {
	videoId: "550e8400-e29b-41d4-a716-446655440099",
	finalVideoId: "550e8400-e29b-41d4-a716-446655440091",
	targetLanguage: "french",
	provider: "ffmpeg",
	format: "mp4",
	quality: "standard",
	duration: 3.5,
	fileSizeBytes: 4096,
	streamUrl:
		"/api/videos/550e8400-e29b-41d4-a716-446655440099/render/french/stream",
	downloadUrl:
		"/api/videos/550e8400-e29b-41d4-a716-446655440099/render/french/stream",
};

export class MockVideoRenderRepository implements VideoRenderRepository {
	async listRenders(videoId: string): Promise<VideoRenderSummary[]> {
		if (videoId !== MOCK_RENDER.videoId) {
			return [];
		}

		return [MOCK_RENDER];
	}

	async getRender(
		videoId: string,
		language: string,
	): Promise<VideoRender | null> {
		if (
			videoId !== MOCK_RENDER.videoId ||
			language !== MOCK_RENDER.targetLanguage
		) {
			return null;
		}

		return MOCK_RENDER;
	}

	async generateRender(
		_videoId: string,
		_request: GenerateVideoRenderRequest,
	): Promise<void> {
		return Promise.resolve();
	}
}
