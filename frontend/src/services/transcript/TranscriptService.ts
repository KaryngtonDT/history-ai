import type { TranscriptLoadResult, TranscriptRepository } from "./TranscriptRepository";
import { createTranscriptRepository } from "./TranscriptRepositoryFactory";
import type { VideoTranscript } from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class TranscriptService {
	private readonly repository: TranscriptRepository;

	constructor(repository: TranscriptRepository) {
		this.repository = repository;
	}

	getTranscript(videoId: string): Promise<VideoTranscript | null> {
		const normalizedVideoId = videoId.trim();

		if (normalizedVideoId === "" || !UUID_PATTERN.test(normalizedVideoId)) {
			return Promise.resolve(null);
		}

		return this.repository.getTranscript(normalizedVideoId);
	}

	loadTranscript(videoId: string): Promise<TranscriptLoadResult> {
		const normalizedVideoId = videoId.trim();

		if (normalizedVideoId === "" || !UUID_PATTERN.test(normalizedVideoId)) {
			return Promise.resolve({ transcript: null });
		}

		return this.repository.loadTranscript(normalizedVideoId);
	}
}

export const transcriptService = new TranscriptService(
	createTranscriptRepository(),
);
