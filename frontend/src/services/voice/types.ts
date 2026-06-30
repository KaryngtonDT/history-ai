import type { TranslationLanguage } from "@/services/translation/types";

export type VoiceCloneProvider = "openvoice" | "seedvc" | "mock";

export type VoiceMode = "generic" | "clone";

export interface VideoVoiceCloneSummary {
	videoId: string;
	artifactId: string;
	sourceAudioId: string;
	clonedAudioId: string;
	targetLanguage: TranslationLanguage;
	provider: VoiceCloneProvider;
	duration: number;
	sampleRate: number;
}

export interface VideoVoiceClone extends VideoVoiceCloneSummary {
	sourceLanguage: TranslationLanguage;
	originalAudioUrl: string;
	clonedAudioUrl: string;
}

export interface GenerateVoiceCloneRequest {
	targetLanguages: TranslationLanguage[];
	provider: VoiceCloneProvider;
	voiceMode: VoiceMode;
}

export const VOICE_CLONE_PROVIDERS: Array<{
	value: VoiceCloneProvider;
	label: string;
}> = [
	{ value: "openvoice", label: "OpenVoice V2" },
	{ value: "mock", label: "Mock" },
];

const VOICE_CLONE_PROVIDER_VALUES = new Set<VoiceCloneProvider>([
	"openvoice",
	"seedvc",
	"mock",
]);

export function mapVoiceCloneProvider(value: string): VoiceCloneProvider {
	return VOICE_CLONE_PROVIDER_VALUES.has(value as VoiceCloneProvider)
		? (value as VoiceCloneProvider)
		: "mock";
}

export function formatVoiceCloneProviderLabel(
	provider: VoiceCloneProvider,
): string {
	return (
		VOICE_CLONE_PROVIDERS.find((entry) => entry.value === provider)?.label ??
		provider
	);
}

export function formatVoiceCloneDuration(seconds: number): string {
	const totalSeconds = Math.max(0, Math.floor(seconds));
	const minutes = Math.floor(totalSeconds / 60);
	const remainder = totalSeconds % 60;

	return `${String(minutes).padStart(2, "0")}:${String(remainder).padStart(2, "0")}`;
}

export function resolveVoiceCloneStreamUrl(
	streamUrl: string,
	apiBaseUrl: string,
): string {
	if (streamUrl.startsWith("http://") || streamUrl.startsWith("https://")) {
		return streamUrl;
	}

	return `${apiBaseUrl.replace(/\/$/, "")}${streamUrl.startsWith("/") ? streamUrl : `/${streamUrl}`}`;
}
