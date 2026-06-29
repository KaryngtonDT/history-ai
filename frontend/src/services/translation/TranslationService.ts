import type { TranslationRepository } from "./TranslationRepository";
import { createTranslationRepository } from "./TranslationRepositoryFactory";
import type {
	GenerateTranslationsRequest,
	TranslationLanguage,
	VideoTranslation,
	VideoTranslationSummary,
} from "./types";

const UUID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class TranslationService {
	private readonly repository: TranslationRepository;

	constructor(repository: TranslationRepository) {
		this.repository = repository;
	}

	listTranslations(videoId: string): Promise<VideoTranslationSummary[]> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve([]);
		}

		return this.repository.listTranslations(videoId.trim());
	}

	getTranslation(
		videoId: string,
		language: TranslationLanguage,
	): Promise<VideoTranslation | null> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.resolve(null);
		}

		return this.repository.getTranslation(videoId.trim(), language);
	}

	generateTranslations(
		videoId: string,
		request: GenerateTranslationsRequest,
	): Promise<void> {
		if (!this.isValidVideoId(videoId)) {
			return Promise.reject(new Error("Invalid video id"));
		}

		if (request.targetLanguages.length === 0) {
			return Promise.reject(new Error("Select at least one target language"));
		}

		return this.repository.generateTranslations(videoId.trim(), request);
	}

	private isValidVideoId(videoId: string): boolean {
		const normalized = videoId.trim();

		return normalized !== "" && UUID_PATTERN.test(normalized);
	}
}

export const translationService = new TranslationService(
	createTranslationRepository(),
);
