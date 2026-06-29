import type {
	GenerateTranslationsRequest,
	VideoTranslation,
	VideoTranslationSummary,
} from "./types";

export interface TranslationRepository {
	listTranslations(videoId: string): Promise<VideoTranslationSummary[]>;
	getTranslation(
		videoId: string,
		language: string,
	): Promise<VideoTranslation | null>;
	generateTranslations(
		videoId: string,
		request: GenerateTranslationsRequest,
	): Promise<void>;
}
