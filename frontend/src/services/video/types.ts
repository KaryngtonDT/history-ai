export type VideoStatus =
	| "uploaded"
	| "queued"
	| "processing"
	| "completed"
	| "failed";

export interface VideoUploadResult {
	videoId: string;
	status: VideoStatus;
}

export interface VideoJobStatus {
	videoId: string;
	status: VideoStatus;
	originalFilename: string;
	language: string;
	createdAt: string;
	failureMessage?: string | null;
	failedStage?: string | null;
	lastProcessingDurationSeconds?: number | null;
}

export interface VideoProcessResult {
	status: "queued" | "unchanged";
}

export type VideoValidationResult =
	| { valid: true }
	| { valid: false; error: string };

export interface VideoUploadApiDto {
	videoId: string;
	status: string;
}

export interface VideoUploadOptions {
	onProgress: (progress: number) => void;
	processingMode?: "manual" | "automatic";
	strategy?: "balanced" | "quality" | "speed" | "low_memory";
}

const VIDEO_STATUSES = new Set<VideoStatus>([
	"uploaded",
	"queued",
	"processing",
	"completed",
	"failed",
]);

export function mapVideoUploadFromApi(
	dto: VideoUploadApiDto,
): VideoUploadResult {
	const status = VIDEO_STATUSES.has(dto.status as VideoStatus)
		? (dto.status as VideoStatus)
		: "queued";

	return {
		videoId: dto.videoId,
		status,
	};
}
