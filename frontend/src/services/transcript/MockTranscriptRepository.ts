import type { TranscriptRepository } from "./TranscriptRepository";
import type { TranscriptLoadResult } from "./TranscriptRepository";
import type { VideoTranscript } from "./types";

export class MockTranscriptRepository implements TranscriptRepository {
	async getTranscript(videoId: string): Promise<VideoTranscript | null> {
		const result = await this.loadTranscript(videoId);

		return result.transcript;
	}

	async loadTranscript(videoId: string): Promise<TranscriptLoadResult> {
		const transcript = await this.loadMockTranscript(videoId);

		return { transcript };
	}

	private async loadMockTranscript(
		videoId: string,
	): Promise<VideoTranscript | null> {
		if (videoId.trim() === "") {
			return null;
		}

		return {
			videoId,
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Welcome to History AI video localization. This is a mock transcript segment.",
			duration: 8.5,
			segmentCount: 2,
			segments: [
				{
					index: 0,
					startTime: 0,
					endTime: 3.5,
					text: "Welcome to History AI video localization.",
				},
				{
					index: 1,
					startTime: 3.5,
					endTime: 8.5,
					text: "This is a mock transcript segment.",
				},
			],
		};
	}
}
