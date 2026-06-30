export type BatchJobStatus =
	| "pending"
	| "running"
	| "completed"
	| "partial_failure"
	| "failed";

export interface ProjectVideo {
	videoId: string;
	filename: string;
	addedAt: string;
}

export interface Project {
	id: string;
	name: string;
	createdAt: string;
	videos: ProjectVideo[];
	batchJobId: string | null;
	batchStatus: BatchJobStatus | null;
	batchProgress: number;
	targetLanguages: string[];
}

export interface BatchJob {
	id: string;
	projectId: string;
	status: BatchJobStatus;
	progress: number;
	totalVideos: number;
	queuedVideos: number;
	targetLanguages: string[];
	failedVideoIds: string[];
}

export interface CreateProjectInput {
	name: string;
}

export interface UpdateProjectInput {
	name: string;
}

export interface ProcessProjectInput {
	targetLanguages: string[];
	processingMode?: string;
	strategy?: string | null;
}

export interface AddProjectVideoInput {
	videoId: string;
	filename?: string;
}

export interface ProjectApiDto {
	id: string;
	name: string;
	createdAt: string;
	videos: ProjectVideoApiDto[];
	batchJobId: string | null;
	batchStatus: BatchJobStatus | null;
	batchProgress: number;
	targetLanguages: string[];
}

export interface ProjectVideoApiDto {
	videoId: string;
	filename: string;
	addedAt: string;
}

export interface BatchJobApiDto {
	id: string;
	projectId: string;
	status: BatchJobStatus;
	progress: number;
	totalVideos: number;
	queuedVideos: number;
	targetLanguages: string[];
	failedVideoIds: string[];
}

export const BATCH_STATUS_LABELS: Record<BatchJobStatus, string> = {
	pending: "Pending",
	running: "Running",
	completed: "Completed",
	partial_failure: "Partial failure",
	failed: "Failed",
};

export const LANGUAGE_LABELS: Record<string, string> = {
	fr: "French",
	de: "German",
	en: "English",
	es: "Spanish",
	it: "Italian",
	french: "French",
	german: "German",
	english: "English",
	spanish: "Spanish",
	italian: "Italian",
};

export const WORKSPACE_TARGET_LANGUAGES = [
	"fr",
	"de",
	"en",
	"es",
	"it",
] as const;

export function mapProjectFromApi(dto: ProjectApiDto): Project {
	return {
		id: dto.id,
		name: dto.name,
		createdAt: dto.createdAt,
		videos: dto.videos.map((video) => ({ ...video })),
		batchJobId: dto.batchJobId,
		batchStatus: dto.batchStatus,
		batchProgress: dto.batchProgress,
		targetLanguages: [...dto.targetLanguages],
	};
}

export function mapBatchJobFromApi(dto: BatchJobApiDto): BatchJob {
	return {
		id: dto.id,
		projectId: dto.projectId,
		status: dto.status,
		progress: dto.progress,
		totalVideos: dto.totalVideos,
		queuedVideos: dto.queuedVideos,
		targetLanguages: [...dto.targetLanguages],
		failedVideoIds: [...dto.failedVideoIds],
	};
}
