import {
	getVideoPipelineStepLabel,
	type VideoPipelineStepId,
} from "@/features/product/videoRoutes";

export type ArtifactStatus = "open" | "generate" | "locked" | "unknown";

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

export function buildArtifactJourney(
	videoId: string | null,
	t: TranslateFn,
): ArtifactJourneyStep[] {
	const base = videoId
		? [
				{
					id: "transcript" as const,
					label: getVideoPipelineStepLabel(t, "transcript"),
					description: t("pipeline.artifactJourney.transcriptDescription"),
					status: "open" as const,
					path: `/video/${videoId}/transcript`,
				},
				{
					id: "translations" as const,
					label: getVideoPipelineStepLabel(t, "translations"),
					description: t("pipeline.artifactJourney.translationsDescription"),
					status: "open" as const,
					path: `/video/${videoId}/translations`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "transcript"),
				},
				{
					id: "audio" as const,
					label: getVideoPipelineStepLabel(t, "audio"),
					description: t("pipeline.artifactJourney.audioDescription"),
					status: "open" as const,
					path: `/video/${videoId}/audio`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "translations"),
				},
				{
					id: "voice-clone" as const,
					label: getVideoPipelineStepLabel(t, "voice-clone"),
					description: t("pipeline.artifactJourney.voiceCloneDescription"),
					status: "open" as const,
					path: `/video/${videoId}/voice-clone`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "audio"),
				},
				{
					id: "lip-sync" as const,
					label: getVideoPipelineStepLabel(t, "lip-sync"),
					description: t("pipeline.artifactJourney.lipSyncDescription"),
					status: "open" as const,
					path: `/video/${videoId}/lip-sync`,
					dependsOnLabel: getVideoPipelineStepLabel(t, "voice-clone"),
				},
				{
					id: "render" as const,
					label: getVideoPipelineStepLabel(t, "render"),
					description: t("pipeline.artifactJourney.renderDescription"),
					status: "open" as const,
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
			status: videoId ? "open" : "generate",
			path: videoId ? undefined : "/video/upload",
		},
		...base,
		{
			id: "quality",
			label: t("pipeline.artifactJourney.qualityLabel"),
			description: t("pipeline.artifactJourney.qualityDescription"),
			status: videoId ? "open" : "locked",
			path: videoId ? `/video/${videoId}` : undefined,
			dependsOnLabel: getVideoPipelineStepLabel(t, "render"),
		},
	];
}
