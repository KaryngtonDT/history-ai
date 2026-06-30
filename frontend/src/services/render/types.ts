import type { TranslationLanguage } from "@/services/translation/types";

export type VideoRenderProvider = "ffmpeg" | "mock";

export type VideoRenderFormat = "mp4" | "webm";

export type VideoRenderQuality = "preview" | "standard" | "high";

export interface VideoRenderSummary {
	videoId: string;
	finalVideoId: string;
	targetLanguage: TranslationLanguage;
	provider: VideoRenderProvider;
	format: VideoRenderFormat;
	quality: VideoRenderQuality;
	duration: number;
	fileSizeBytes: number;
	streamUrl: string;
}

export interface VideoRender extends VideoRenderSummary {
	downloadUrl: string;
}

export interface GenerateVideoRenderRequest {
	targetLanguages: TranslationLanguage[];
	provider?: VideoRenderProvider;
	format?: VideoRenderFormat;
	quality?: VideoRenderQuality;
}

export interface VideoRenderProviderOption {
	value: VideoRenderProvider;
	label: string;
	enabled: boolean;
}

export const VIDEO_RENDER_PROVIDERS: VideoRenderProviderOption[] = [
	{ value: "ffmpeg", label: "FFmpeg", enabled: true },
];

export const VIDEO_RENDER_FORMATS: VideoRenderFormat[] = ["mp4", "webm"];

export const VIDEO_RENDER_QUALITIES: VideoRenderQuality[] = [
	"preview",
	"standard",
	"high",
];

const VIDEO_RENDER_PROVIDER_VALUES = new Set<VideoRenderProvider>([
	"ffmpeg",
	"mock",
]);

const VIDEO_RENDER_FORMAT_VALUES = new Set<VideoRenderFormat>(["mp4", "webm"]);

const VIDEO_RENDER_QUALITY_VALUES = new Set<VideoRenderQuality>([
	"preview",
	"standard",
	"high",
]);

export function mapVideoRenderProvider(value: string): VideoRenderProvider {
	return VIDEO_RENDER_PROVIDER_VALUES.has(value as VideoRenderProvider)
		? (value as VideoRenderProvider)
		: "mock";
}

export function mapVideoRenderFormat(value: string): VideoRenderFormat {
	return VIDEO_RENDER_FORMAT_VALUES.has(value as VideoRenderFormat)
		? (value as VideoRenderFormat)
		: "mp4";
}

export function mapVideoRenderQuality(value: string): VideoRenderQuality {
	return VIDEO_RENDER_QUALITY_VALUES.has(value as VideoRenderQuality)
		? (value as VideoRenderQuality)
		: "standard";
}

export function formatVideoRenderProviderLabel(
	provider: VideoRenderProvider,
): string {
	const match = VIDEO_RENDER_PROVIDERS.find(
		(entry) => entry.value === provider,
	);

	return match?.label ?? provider;
}

export function formatVideoRenderDuration(seconds: number): string {
	const minutes = Math.floor(seconds / 60);
	const remaining = Math.round(seconds % 60);

	return `${minutes}:${remaining.toString().padStart(2, "0")}`;
}

export function formatFileSize(bytes: number): string {
	if (bytes < 1024) {
		return `${bytes} B`;
	}

	if (bytes < 1024 * 1024) {
		return `${(bytes / 1024).toFixed(1)} KB`;
	}

	return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

export function resolveVideoRenderStreamUrl(
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
