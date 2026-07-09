import {
	findPipelineJobForStage,
	mapPipelineJobToArtifactStatus,
	PIPELINE_STAGE_BY_STEP,
} from "@/features/pipeline/pipelineJobStateUtils";
import {
	getVideoPipelineStepLabel,
	type VideoPipelineStepId,
} from "@/features/product/videoRoutes";
import type { PipelineSourceStatus } from "@/services/pipeline/jobTypes";
import type { VideoPipelineProgress } from "./useVideoPipelineProgress";

export type ArtifactStatus =
	| "completed"
	| "in_progress"
	| "failed"
	| "open"
	| "generate"
	| "locked"
	| "unknown";

export interface ArtifactJourneyStep {
	id: VideoPipelineStepId | "video" | "quality";
	label: string;
	description: string;
	status: ArtifactStatus;
	path?: string;
	dependsOnLabel?: string;
}

type TranslateFn = (
	key: string,
	params?: Record<string, string | number>,
) => string;

function resolveStepStatusFromPipeline(
	pipelineStatus: PipelineSourceStatus | null | undefined,
	stepId: VideoPipelineStepId,
	dependencyMet: boolean,
	artifactCompleted: boolean,
): ArtifactStatus {
	const stage = PIPELINE_STAGE_BY_STEP[stepId];
	const job = findPipelineJobForStage(pipelineStatus, stage);

	if (job) {
		return mapPipelineJobToArtifactStatus(job, dependencyMet);
	}

	if (!dependencyMet) {
		return "locked";
	}

	return artifactCompleted ? "completed" : "open";
}

export function buildArtifactJourney(
	videoId: string | null,
	t: TranslateFn,
	progress?: VideoPipelineProgress,
	pipelineStatus?: PipelineSourceStatus | null,
): ArtifactJourneyStep[] {
	const hasTranscript = progress?.hasTranscript ?? false;
	const hasTranslations = progress?.hasTranslations ?? false;
	const hasAudio = progress?.hasAudio ?? false;
	const hasVoiceClone = progress?.hasVoiceClone ?? false;
	const hasLipSync = progress?.hasLipSync ?? false;
	const hasRender = progress?.hasRender ?? false;

	const base = videoId
		? [
				{
					id: "transcript" as const,
					label: getVideoPipelineStepLabel(t, "transcript"),
					description: t("pipeline.artifactJourney.transcriptDescription"),
					status: resolveStepStatusFromPipeline(
						pipelineStatus,
						"transcript",
						true,
						hasTranscript,
					),
					path: `/video/${videoId}/transcript`,
				},
				{
					id: "translations" as const,
					label: getVideoPipelineStepLabel(t, "translations"),
					description: t("pipeline.artifactJourney.translationsDescription"),
					status: resolveStepStatusFromPipeline(
						pipelineStatus,
						"translations",
						hasTranscript,
						hasTranslations,
					),
					path: `/video/${videoId}/translations`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "transcript"),
				},
				{
					id: "audio" as const,
					label: getVideoPipelineStepLabel(t, "audio"),
					description: t("pipeline.artifactJourney.audioDescription"),
					status: resolveStepStatusFromPipeline(
						pipelineStatus,
						"audio",
						hasTranslations,
						hasAudio,
					),
					path: `/video/${videoId}/audio`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "translations"),
				},
				{
					id: "voice-clone" as const,
					label: getVideoPipelineStepLabel(t, "voice-clone"),
					description: t("pipeline.artifactJourney.voiceCloneDescription"),
					status: resolveStepStatusFromPipeline(
						pipelineStatus,
						"voice-clone",
						hasAudio,
						hasVoiceClone,
					),
					path: `/video/${videoId}/voice-clone`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "audio"),
				},
				{
					id: "lip-sync" as const,
					label: getVideoPipelineStepLabel(t, "lip-sync"),
					description: t("pipeline.artifactJourney.lipSyncDescription"),
					status: resolveStepStatusFromPipeline(
						pipelineStatus,
						"lip-sync",
						hasVoiceClone,
						hasLipSync,
					),
					path: `/video/${videoId}/lip-sync`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "voice-clone"),
				},
				{
					id: "render" as const,
					label: getVideoPipelineStepLabel(t, "render"),
					description: t("pipeline.artifactJourney.renderDescription"),
					status: resolveStepStatusFromPipeline(
						pipelineStatus,
						"render",
						hasLipSync,
						hasRender,
					),
					path: `/video/${videoId}/render`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "lip-sync"),
				},
			]
		: [];

	return [
		{
			id: "video",
			label: t("pipeline.artifactJourney.videoLabel"),
			description: t("pipeline.artifactJourney.videoDescription"),
			status: videoId ? "completed" : "generate",
			path: videoId ? undefined : "/video/upload",
		},
		...base,
		{
			id: "quality",
			label: t("pipeline.artifactJourney.qualityLabel"),
			description: t("pipeline.artifactJourney.qualityDescription"),
			status: videoId
				? resolveStepStatusFromPipeline(
						pipelineStatus,
						"render",
						hasRender,
						hasRender,
					)
				: "locked",
			path: videoId ? `/video/${videoId}` : undefined,
			dependsOnLabel: getVideoPipelineStepLabel(t, "render"),
		},
	];
}
