import type { TranslationRepository } from "./TranslationRepository";
import type {
	GenerateTranslationsRequest,
	TranslationLanguage,
	VideoTranslation,
	VideoTranslationSummary,
} from "./types";

export class MockTranslationRepository implements TranslationRepository {
	private readonly translations = new Map<string, VideoTranslationSummary[]>();

	async listTranslations(videoId: string): Promise<VideoTranslationSummary[]> {
		return this.translations.get(videoId) ?? [];
	}

	async getTranslation(
		videoId: string,
		language: string,
	): Promise<VideoTranslation | null> {
		const summaries = this.translations.get(videoId) ?? [];
		const summary = summaries.find(
			(entry) => entry.targetLanguage === language,
		);

		if (!summary) {
			return null;
		}

		return {
			...summary,
			segments: [
				{
					index: 0,
					sourceText: "Hello everyone",
					translatedText: `[${language}] Hello everyone`,
				},
			],
		};
	}

	async generateTranslations(
		videoId: string,
		request: GenerateTranslationsRequest,
	): Promise<void> {
		const existing = this.translations.get(videoId) ?? [];
		const generated = request.targetLanguages.map((language, index) =>
			this.createSummary(videoId, language, index),
		);

		this.translations.set(videoId, [...existing, ...generated]);
	}

	private createSummary(
		videoId: string,
		language: TranslationLanguage,
		index: number,
	): VideoTranslationSummary {
		return {
			videoId,
			translationId: `00000000-0000-4000-8000-${String(index).padStart(12, "0")}`,
			sourceLanguage: "english",
			targetLanguage: language,
			provider: "mock",
			text: `[${language}] Mock translation`,
			segmentCount: 1,
		};
	}
}
