import type { TranslationLanguage } from "@/services/translation/types";

export type LipSyncProvider = "latentsync" | "wav2lip" | "mock";

export interface VideoLipSyncSummary {
	videoId: string;
	artifactId: string;
	clonedAudioId: string;
	targetLanguage: TranslationLanguage;
	provider: LipSyncProvider;
	synchronizedVideoId: string;
	duration: number;
	syncedVideoUrl: string;
}

export interface VideoLipSync extends VideoLipSyncSummary {
	originalVideoUrl: string;
}

export interface GenerateLipSyncRequest {
	targetLanguages: TranslationLanguage[];
	provider: LipSyncProvider;
}

export interface LipSyncProviderOption {
	value: LipSyncProvider;
	label: string;
	enabled: boolean;
}

export const LIP_SYNC_PROVIDERS: LipSyncProviderOption[] = [
	{ value: "latentsync", label: "LatentSync", enabled: true },
	{ value: "wav2lip", label: "Wav2Lip", enabled: false },
];

const LIP_SYNC_PROVIDER_VALUES = new Set<LipSyncProvider>([
	"latentsync",
	"wav2lip",
	"mock",
]);

export function mapLipSyncProvider(value: string): LipSyncProvider {
	return LIP_SYNC_PROVIDER_VALUES.has(value as LipSyncProvider)
		? (value as LipSyncProvider)
		: "mock";
}

export function formatLipSyncProviderLabel(provider: LipSyncProvider): string {
	const match = LIP_SYNC_PROVIDERS.find((entry) => entry.value === provider);

	return match?.label ?? provider;
}

export function formatLipSyncDuration(seconds: number): string {
	const minutes = Math.floor(seconds / 60);
	const remaining = Math.round(seconds % 60);

	return `${minutes}:${remaining.toString().padStart(2, "0")}`;
}

export function resolveLipSyncStreamUrl(
	path: string,
	apiBaseUrl: string,
): string {
	if (path.startsWith("http://") || path.startsWith("https://")) {
		return path;
	}

	const normalizedBase = apiBaseUrl.endsWith("/")
		? apiBaseUrl.slice(0, -1)
		: apiBaseUrl;

	return `${normalizedBase}${path.startsWith("/") ? path : `/${path}`}`;
}
