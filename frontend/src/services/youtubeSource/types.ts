export interface YouTubeMetadata {
	title: string;
	durationSeconds: number | null;
	thumbnailUrl: string | null;
	language: string | null;
	channelName: string | null;
}

export interface YouTubeImport {
	youtubeId: string;
	videoId: string;
	url: string;
	videoStatus: string;
	importedAt: string;
	metadata: YouTubeMetadata;
}

export interface YouTubeImportResult {
	youtubeId: string;
	videoId: string;
	status: string;
	url: string;
	metadata: YouTubeMetadata;
}

export interface YouTubeImportOptions {
	processingMode?: "manual" | "automatic";
	strategy?: string;
}

const YOUTUBE_URL_PATTERN =
	/^https?:\/\/(?:www\.|m\.)?(?:youtube\.com\/(?:watch\?v=|shorts\/|embed\/)|youtu\.be\/)[\w-]{11}/i;

export function validateYouTubeUrl(url: string): {
	valid: boolean;
	error?: string;
} {
	const trimmed = url.trim();

	if (!trimmed) {
		return { valid: false, error: "Paste a YouTube URL to continue." };
	}

	if (!YOUTUBE_URL_PATTERN.test(trimmed)) {
		return {
			valid: false,
			error: "Use a valid youtube.com or youtu.be link.",
		};
	}

	return { valid: true };
}

export function formatDuration(seconds: number | null): string {
	if (seconds === null || seconds <= 0) {
		return "Unknown duration";
	}

	const minutes = Math.floor(seconds / 60);
	const remainder = seconds % 60;

	return `${minutes}:${remainder.toString().padStart(2, "0")}`;
}
