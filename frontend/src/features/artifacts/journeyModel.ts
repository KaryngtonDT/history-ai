import {
	getVideoPipelineStepLabel,
	type VideoPipelineStepId,
} from "@/features/product/videoRoutes";
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

function isProcessing(videoStatus: VideoPipelineProgress["videoStatus"]): boolean {
	return videoStatus === "queued" || videoStatus === "processing";
}

function resolveStepStatus(
	completed: boolean,
	dependencyMet: boolean,
	videoStatus: VideoPipelineProgress["videoStatus"],
): ArtifactStatus {
	if (completed) {
		return "completed";
	}

	if (!dependencyMet) {
		return "locked";
	}

	if (videoStatus === "failed") {
		return "failed";
	}

	if (isProcessing(videoStatus)) {
		return "in_progress";
	}

	return "open";
}

export function buildArtifactJourney(
	videoId: string | null,
	t: TranslateFn,
	progress?: VideoPipelineProgress,
): ArtifactJourneyStep[] {
	const videoStatus = progress?.videoStatus ?? null;
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
					status: resolveStepStatus(hasTranscript, true, videoStatus),
					path: `/video/${videoId}/transcript`,
				},
				{
					id: "translations" as const,
					label: getVideoPipelineStepLabel(t, "translations"),
					description: t("pipeline.artifactJourney.translationsDescription"),
					status: resolveStepStatus(
						hasTranslations,
						hasTranscript,
						videoStatus,
					),
					path: `/video/${videoId}/translations`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "transcript"),
				},
				{
					id: "audio" as const,
					label: getVideoPipelineStepLabel(t, "audio"),
					description: t("pipeline.artifactJourney.audioDescription"),
					status: resolveStepStatus(hasAudio, hasTranslations, videoStatus),
					path: `/video/${videoId}/audio`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "translations"),
				},
				{
					id: "voice-clone" as const,
					label: getVideoPipelineStepLabel(t, "voice-clone"),
					description: t("pipeline.artifactJourney.voiceCloneDescription"),
					status: resolveStepStatus(hasVoiceClone, hasAudio, videoStatus),
					path: `/video/${videoId}/voice-clone`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "audio"),
				},
				{
					id: "lip-sync" as const,
					label: getVideoPipelineStepLabel(t, "lip-sync"),
					description: t("pipeline.artifactJourney.lipSyncDescription"),
					status: resolveStepStatus(hasLipSync, hasVoiceClone, videoStatus),
					path: `/video/${videoId}/lip-sync`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "voice-clone"),
				},
				{
					id: "render" as const,
					label: getVideoPipelineStepLabel(t, "render"),
					description: t("pipeline.artifactJourney.renderDescription"),
					status: resolveStepStatus(hasRender, hasLipSync, videoStatus),
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
				? resolveStepStatus(hasRender, hasRender, videoStatus)
				: "locked",
			path: videoId ? `/video/${videoId}` : undefined,
			dependsOnLabel: getVideoPipelineStepLabel(t, "render"),
		},
	];
}
