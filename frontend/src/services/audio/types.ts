import type { TranslationLanguage } from "@/services/translation/types";

export type TextToSpeechProvider = "f5_tts" | "kokoro" | "xtts" | "mock";

export type VoiceGender = "male" | "female" | "neutral";

export interface VoiceOption {
	voiceId: string;
	displayName: string;
	language: TranslationLanguage;
	gender: VoiceGender;
}

export interface VideoAudioSummary {
	videoId: string;
	audioId: string;
	translationId: string;
	targetLanguage: TranslationLanguage;
	provider: TextToSpeechProvider;
	voiceId: string;
	voiceDisplayName: string;
	duration: number;
	format: string;
}

export interface VideoAudio extends VideoAudioSummary {
	voiceLanguage: string;
	voiceGender: VoiceGender;
	downloadUrl: string;
}

export interface GenerateAudioRequest {
	targetLanguages: TranslationLanguage[];
	provider: TextToSpeechProvider;
	voiceId: string;
}

export const TTS_PROVIDERS: Array<{
	value: TextToSpeechProvider;
	label: string;
}> = [
	{ value: "f5_tts", label: "F5-TTS" },
	{ value: "mock", label: "Mock" },
];

export const AVAILABLE_VOICES: VoiceOption[] = [
	{
		voiceId: "female_01",
		displayName: "Female 01",
		language: "french",
		gender: "female",
	},
	{
		voiceId: "male_01",
		displayName: "Male 01",
		language: "french",
		gender: "male",
	},
	{
		voiceId: "female_en_01",
		displayName: "Female EN 01",
		language: "english",
		gender: "female",
	},
	{
		voiceId: "female_de_01",
		displayName: "Female DE 01",
		language: "german",
		gender: "female",
	},
];

const TTS_PROVIDER_VALUES = new Set<TextToSpeechProvider>([
	"f5_tts",
	"kokoro",
	"xtts",
	"mock",
]);

export function mapTextToSpeechProvider(value: string): TextToSpeechProvider {
	return TTS_PROVIDER_VALUES.has(value as TextToSpeechProvider)
		? (value as TextToSpeechProvider)
		: "mock";
}

export function formatTextToSpeechProviderLabel(
	provider: TextToSpeechProvider,
): string {
	return (
		TTS_PROVIDERS.find((entry) => entry.value === provider)?.label ?? provider
	);
}

export function formatAudioDuration(seconds: number): string {
	const totalSeconds = Math.max(0, Math.floor(seconds));
	const minutes = Math.floor(totalSeconds / 60);
	const remainder = totalSeconds % 60;

	return `${String(minutes).padStart(2, "0")}:${String(remainder).padStart(2, "0")}`;
}

export function resolveAudioStreamUrl(
	downloadUrl: string,
	apiBaseUrl: string,
): string {
	if (downloadUrl.startsWith("http://") || downloadUrl.startsWith("https://")) {
		return downloadUrl;
	}

	return `${apiBaseUrl.replace(/\/$/, "")}${downloadUrl.startsWith("/") ? downloadUrl : `/${downloadUrl}`}`;
}
