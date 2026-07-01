import type { AudioSourceRepository } from "./AudioSourceRepository";
import type {
	AudioSource,
	AudioUploadOptions,
	AudioUploadResult,
} from "./types";

const MOCK_SOURCES: AudioSource[] = [
	{
		id: "6ba7b810-9dad-11d1-80b4-00c04fd430c8",
		title: "History Podcast Ep.1",
		originalFilename: "history-podcast-ep1.mp3",
		status: "completed",
		type: "audio",
		createdAt: "2026-07-01T10:00:00+00:00",
	},
];

export class MockAudioSourceRepository implements AudioSourceRepository {
	async listAudioSources(): Promise<AudioSource[]> {
		return [...MOCK_SOURCES];
	}

	async getAudioSource(audioId: string): Promise<AudioSource | null> {
		return MOCK_SOURCES.find((source) => source.id === audioId) ?? null;
	}

	async uploadAudio(
		_file: File,
		_options?: AudioUploadOptions,
	): Promise<AudioUploadResult> {
		return {
			audioId: "6ba7b811-9dad-11d1-80b4-00c04fd430c8",
			status: "queued",
		};
	}
}
