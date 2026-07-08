import type { PipelineStageType } from "./types";

export type PipelineJobStatus =
	| "queued"
	| "running"
	| "completed"
	| "failed"
	| "cancelled"
	| "waiting_user_confirmation"
	| "waiting_user_choice";

export type TranscriptUserChoice =
	| "youtube_transcript"
	| "local_engine"
	| "none";

export interface PipelineJob {
	jobId: string;
	sourceId: string;
	videoId?: string | null;
	stage: PipelineStageType;
	status: PipelineJobStatus;
	progressPercent: number;
	currentStep?: string | null;
	currentEngine?: string | null;
	provider?: string | null;
	startedAt?: string | null;
	updatedAt?: string;
	completedAt?: string | null;
	estimatedDurationSeconds?: number | null;
	estimatedRemainingSeconds?: number | null;
	elapsedSeconds?: number | null;
	failureReason?: string | null;
	cancellationReason?: string | null;
	transcriptSource?: string | null;
	userChoiceRequired?: boolean;
	userChoiceOptions?: string[];
	staleArtifactIds?: string[];
}

export interface PipelineSourceStatus {
	sourceId: string;
	activeJobs: PipelineJob[];
	completedJobs: PipelineJob[];
	jobsWaitingUserChoice: PipelineJob[];
	jobsWaitingConfirmation: PipelineJob[];
	failedJobs: PipelineJob[];
	cancelledJobs: PipelineJob[];
	staleArtifacts: string[];
	nextPossibleStage?: PipelineStageType | null;
	blockedStages: string[];
	requiresUserAction: boolean;
	message: string;
	notification?: string | null;
}

export interface PipelineNotification {
	notificationId: string;
	sourceId: string;
	stage?: PipelineStageType | null;
	type: string;
	title: string;
	message: string;
	read: boolean;
	createdAt: string;
	actionUrl?: string | null;
}

export interface TranscriptMetadata {
	transcriptSource?: string;
	sourceLanguage?: string;
	confidence?: number | null;
	generatedAt?: string | null;
	selectedByUser?: boolean;
	fallbackReason?: string | null;
	originalCaptionAvailable?: boolean;
	userChoice?: TranscriptUserChoice | null;
}
