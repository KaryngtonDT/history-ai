import type { TranslationLanguage } from "@/services/translation/types";
import type { LipSyncRepository } from "./LipSyncRepository";
import { createLipSyncRepository } from "./LipSyncRepositoryFactory";
import type {
	GenerateLipSyncRequest,
	VideoLipSync,
	VideoLipSyncSummary,
} from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class LipSyncService {
	private readonly repository: LipSyncRepository;

	constructor(repository: LipSyncRepository) {
		this.repository = repository;
	}

	listLipSyncs(videoId: string): Promise<VideoLipSyncSummary[]> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve([]);
		}

		return this.repository.listLipSyncs(videoId.trim());
	}

	getLipSync(
		videoId: string,
		language: TranslationLanguage | string,
	): Promise<VideoLipSync | null> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve(null);
		}

		return this.repository.getLipSync(videoId.trim(), language);
	}

	generateLipSync(
		videoId: string,
		request: GenerateLipSyncRequest,
	): Promise<void> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.reject(new Error("Invalid video id"));
		}

		if (request.targetLanguages.length === 0) {
			return Promise.reject(new Error("Select at least one target language"));
		}

		return this.repository.generateLipSync(videoId.trim(), request);
	}

	private isValidVideoId(videoId: string): boolean {
		const normalized = videoId.trim();

		return normalized !== "" && UUID_PATTERN.test(normalized);
	}
}

export const lipSyncService = new LipSyncService(createLipSyncRepository());
