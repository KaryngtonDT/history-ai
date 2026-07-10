export type TranslationLanguage =
	| "english"
	| "french"
	| "german"
	| "spanish"
	| "italian"
	| "unknown";

export type TranslationProvider =
	| "qwen"
	| "deepseek"
	| "gemini"
	| "gpt"
	| "mock";

export interface TranslationSegment {
	index: number;
	sourceText: string;
	translatedText: string;
}

export interface VideoTranslation {
	videoId: string;
	translationId: string;
	sourceLanguage: TranslationLanguage;
	targetLanguage: TranslationLanguage;
	provider: TranslationProvider;
	text: string;
	segmentCount: number;
	segments: TranslationSegment[];
}

export interface VideoTranslationSummary {
	videoId: string;
	translationId: string;
	sourceLanguage: TranslationLanguage;
	targetLanguage: TranslationLanguage;
	provider: TranslationProvider;
	text: string;
	segmentCount: number;
}

export interface GenerateTranslationsRequest {
	targetLanguages: TranslationLanguage[];
	provider: TranslationProvider;
}

export const TARGET_TRANSLATION_LANGUAGES: TranslationLanguage[] = [
	"french",
	"german",
	"spanish",
	"italian",
];

export const TRANSLATION_PROVIDERS: Array<{
	value: TranslationProvider;
	label: string;
}> = [
	{ value: "qwen", label: "Qwen 3 (Ollama)" },
	{ value: "deepseek", label: "DeepSeek R1 (Ollama)" },
];

const TRANSLATION_LANGUAGES = new Set<TranslationLanguage>([
	"english",
	"french",
	"german",
	"spanish",
	"italian",
	"unknown",
]);

const TRANSLATION_PROVIDER_VALUES = new Set<TranslationProvider>([
	"qwen",
	"deepseek",
	"gemini",
	"gpt",
	"mock",
]);

export function mapTranslationLanguage(value: string): TranslationLanguage {
	return TRANSLATION_LANGUAGES.has(value as TranslationLanguage)
		? (value as TranslationLanguage)
		: "unknown";
}

export function mapTranslationProvider(value: string): TranslationProvider {
	return TRANSLATION_PROVIDER_VALUES.has(value as TranslationProvider)
		? (value as TranslationProvider)
		: "qwen";
}

export function formatTranslationLanguageLabel(
	language: TranslationLanguage,
): string {
	return language.charAt(0).toUpperCase() + language.slice(1);
}

export function formatTranslationProviderLabel(
	provider: TranslationProvider,
): string {
	return provider.toUpperCase();
}
