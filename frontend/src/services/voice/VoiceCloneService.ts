import type { TranslationLanguage } from "@/services/translation/types";
import type {
	GenerateVoiceCloneRequest,
	VideoVoiceClone,
	VideoVoiceCloneSummary,
} from "./types";
import type { VoiceCloneRepository } from "./VoiceCloneRepository";
import { createVoiceCloneRepository } from "./VoiceCloneRepositoryFactory";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class VoiceCloneService {
	private readonly repository: VoiceCloneRepository;

	constructor(repository: VoiceCloneRepository) {
		this.repository = repository;
	}

	listVoiceClones(videoId: string): Promise<VideoVoiceCloneSummary[]> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve([]);
		}

		return this.repository.listVoiceClones(videoId.trim());
	}

	getVoiceClone(
		videoId: string,
		language: TranslationLanguage,
	): Promise<VideoVoiceClone | null> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve(null);
		}

		return this.repository.getVoiceClone(videoId.trim(), language);
	}

	generateVoiceClone(
		videoId: string,
		request: GenerateVoiceCloneRequest,
	): Promise<void> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.reject(new Error("Invalid video id"));
		}

		if (request.targetLanguages.length === 0) {
			return Promise.reject(new Error("Select at least one target language"));
		}

		if (request.voiceMode !== "clone") {
			return Promise.reject(new Error("Voice mode must be clone"));
		}

		return this.repository.generateVoiceClone(videoId.trim(), request);
	}

	private isValidVideoId(videoId: string): boolean {
		const normalized = videoId.trim();

		return normalized !== "" && UUID_PATTERN.test(normalized);
	}
}

export const voiceCloneService = new VoiceCloneService(
	createVoiceCloneRepository(),
);
