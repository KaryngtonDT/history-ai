import type { VideoPipelineStepId } from "@/features/product/videoRoutes";
import type {
	PipelineJob,
	PipelineJobStatus,
	PipelineSourceStatus,
} from "@/services/pipeline/jobTypes";

export const PIPELINE_STAGE_BY_STEP: Record<VideoPipelineStepId, string> = {
	transcript: "speech_to_text",
	translations: "translation",
	audio: "text_to_speech",
	"voice-clone": "voice_clone",
	"lip-sync": "lip_sync",
	render: "video_render",
};

export type PipelineStageUiState =
	| "not_started"
	| "queued"
	| "running"
	| "waiting_user_confirmation"
	| "completed"
	| "failed"
	| "cancelled";

export function findPipelineJobForStage(
	status: PipelineSourceStatus | null | undefined,
	stage: string,
): PipelineJob | null {
	if (!status) {
		return null;
	}

	const pools = [
		...(status.activeJobs ?? []),
		...(status.jobsWaitingConfirmation ?? []),
		...(status.completedJobs ?? []),
		...(status.failedJobs ?? []),
		...(status.cancelledJobs ?? []),
		...(status.jobsWaitingUserChoice ?? []),
	];

	return pools.find((job) => job.stage === stage) ?? null;
}

export function resolvePipelineStageUiState(
	job: PipelineJob | null,
): PipelineStageUiState {
	if (!job) {
		return "not_started";
	}

	switch (job.status as PipelineJobStatus) {
		case "queued":
		case "waiting_user_choice":
			return "queued";
		case "running":
			return "running";
		case "waiting_user_confirmation":
			return "waiting_user_confirmation";
		case "completed":
			return "completed";
		case "failed":
			return "failed";
		case "cancelled":
			return "cancelled";
		default:
			return "not_started";
	}
}

export function isPipelineStageExecutionLocked(
	status: PipelineSourceStatus | null | undefined,
	stage: string,
): boolean {
	const job = findPipelineJobForStage(status, stage);

	if (!job) {
		return false;
	}

	return ["queued", "running", "waiting_user_confirmation"].includes(
		job.status,
	);
}

export function mapPipelineJobToArtifactStatus(
	job: PipelineJob | null,
	dependencyMet: boolean,
): import("@/features/artifacts/journeyModel").ArtifactStatus {
	if (!job) {
		return dependencyMet ? "open" : "locked";
	}

	const uiState = resolvePipelineStageUiState(job);
	const isActive = ["queued", "running", "waiting_user_confirmation"].includes(
		uiState,
	);

	if (!dependencyMet && !isActive) {
		return "locked";
	}

	switch (uiState) {
		case "completed":
		case "waiting_user_confirmation":
			return "completed";
		case "running":
		case "queued":
			return "in_progress";
		case "failed":
			return "failed";
		case "cancelled":
			return "open";
		default:
			return dependencyMet ? "open" : "locked";
	}
}

const PIPELINE_STEP_ORDER: VideoPipelineStepId[] = [
	"transcript",
	"translations",
	"audio",
	"voice-clone",
	"lip-sync",
	"render",
];

export function inferArtifactDependencyMet(
	pipelineStatus: PipelineSourceStatus | null | undefined,
	stepId: VideoPipelineStepId,
	artifactPresent: boolean,
): boolean {
	if (artifactPresent) {
		return true;
	}

	if (!pipelineStatus) {
		return false;
	}

	const stepIndex = PIPELINE_STEP_ORDER.indexOf(stepId);

	if (stepIndex <= 0) {
		return true;
	}

	for (let index = stepIndex; index < PIPELINE_STEP_ORDER.length; index += 1) {
		const stage = PIPELINE_STAGE_BY_STEP[PIPELINE_STEP_ORDER[index]];
		const job = findPipelineJobForStage(pipelineStatus, stage);

		if (!job || job.status === "cancelled") {
			continue;
		}

		return true;
	}

	return false;
}

export function collectAllPipelineJobs(
	status: PipelineSourceStatus | null | undefined,
): PipelineJob[] {
	if (!status) {
		return [];
	}

	return [
		...(status.activeJobs ?? []),
		...(status.jobsWaitingConfirmation ?? []),
		...(status.completedJobs ?? []),
		...(status.failedJobs ?? []),
		...(status.cancelledJobs ?? []),
		...(status.jobsWaitingUserChoice ?? []),
	];
}
