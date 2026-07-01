import type {
	YouTubeImport,
	YouTubeImportOptions,
	YouTubeImportResult,
	YouTubeMetadata,
} from "./types";
import type { YouTubeSourceRepository } from "./YouTubeSourceRepository";

const MOCK_METADATA: YouTubeMetadata = {
	title: "History Lecture on YouTube",
	durationSeconds: 600,
	thumbnailUrl: "https://img.youtube.com/vi/dQw4w9WgXcQ/hqdefault.jpg",
	language: "en",
	channelName: "History Channel",
};

export class MockYouTubeSourceRepository implements YouTubeSourceRepository {
	async previewYouTube(_url: string): Promise<YouTubeMetadata> {
		return MOCK_METADATA;
	}

	async importYouTube(
		url: string,
		_options?: YouTubeImportOptions,
	): Promise<YouTubeImportResult> {
		return {
			youtubeId: "6ba7b812-9dad-11d1-80b4-00c04fd430c8",
			videoId: "6ba7b813-9dad-11d1-80b4-00c04fd430c8",
			status: "queued",
			url,
			metadata: MOCK_METADATA,
		};
	}

	async listYouTubeImports(): Promise<YouTubeImport[]> {
		return [
			{
				youtubeId: "6ba7b812-9dad-11d1-80b4-00c04fd430c8",
				videoId: "6ba7b813-9dad-11d1-80b4-00c04fd430c8",
				url: "https://www.youtube.com/watch?v=dQw4w9WgXcQ",
				videoStatus: "queued",
				importedAt: "2026-07-02T10:00:00+00:00",
				metadata: MOCK_METADATA,
			},
		];
	}

	async getYouTubeImport(youtubeId: string): Promise<YouTubeImport | null> {
		const items = await this.listYouTubeImports();
		return items.find((item) => item.youtubeId === youtubeId) ?? null;
	}
}
