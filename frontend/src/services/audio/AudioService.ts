import type { TranslationLanguage } from "@/services/translation/types";
import type { AudioRepository } from "./AudioRepository";
import { createAudioRepository } from "./AudioRepositoryFactory";
import type {
	GenerateAudioRequest,
	VideoAudio,
	VideoAudioSummary,
} from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class AudioService {
	private readonly repository: AudioRepository;

	constructor(repository: AudioRepository) {
		this.repository = repository;
	}

	listAudio(videoId: string): Promise<VideoAudioSummary[]> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve([]);
		}

		return this.repository.listAudio(videoId.trim());
	}

	getAudio(
		videoId: string,
		language: TranslationLanguage,
	): Promise<VideoAudio | null> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve(null);
		}

		return this.repository.getAudio(videoId.trim(), language);
	}

	generateAudio(videoId: string, request: GenerateAudioRequest): Promise<void> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.reject(new Error("Invalid video id"));
		}

		if (request.targetLanguages.length === 0) {
			return Promise.reject(new Error("Select at least one target language"));
		}

		if (request.voiceId.trim() === "") {
			return Promise.reject(new Error("Select a voice"));
		}

		return this.repository.generateAudio(videoId.trim(), request);
	}

	private isValidVideoId(videoId: string): boolean {
		const normalized = videoId.trim();

		return normalized !== "" && UUID_PATTERN.test(normalized);
	}
}

export const audioService = new AudioService(createAudioRepository());
