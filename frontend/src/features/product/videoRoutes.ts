export type VideoPipelineStepId =
	| "transcript"
	| "translations"
	| "audio"
	| "voice-clone"
	| "lip-sync"
	| "render";

export interface VideoPipelineStep {
	id: VideoPipelineStepId;
	path: (videoId: string) => string;
	sprint: number;
	dependsOn?: VideoPipelineStepId;
}

export const VIDEO_PIPELINE_STEPS: VideoPipelineStep[] = [
	{
		id: "transcript",
		path: (videoId) => `/video/${videoId}/transcript`,
		sprint: 32,
	},
	{
		id: "translations",
		path: (videoId) => `/video/${videoId}/translations`,
		sprint: 33,
		dependsOn: "transcript",
	},
	{
		id: "audio",
		path: (videoId) => `/video/${videoId}/audio`,
		sprint: 35,
		dependsOn: "translations",
	},
	{
		id: "voice-clone",
		path: (videoId) => `/video/${videoId}/voice-clone`,
		sprint: 36,
		dependsOn: "audio",
	},
	{
		id: "lip-sync",
		path: (videoId) => `/video/${videoId}/lip-sync`,
		sprint: 37,
		dependsOn: "voice-clone",
	},
	{
		id: "render",
		path: (videoId) => `/video/${videoId}/render`,
		sprint: 38,
		dependsOn: "lip-sync",
	},
];

export function videoPipelinePath(
	stepId: VideoPipelineStepId,
	videoId: string,
): string {
	const step = VIDEO_PIPELINE_STEPS.find((entry) => entry.id === stepId);
	return step ? step.path(videoId) : `/video/${videoId}/transcript`;
}

type TranslateFn = (
	key: string,
	params?: Record<string, string | number>,
) => string;

export function getVideoPipelineStepLabel(
	t: TranslateFn,
	stepId: VideoPipelineStepId,
): string {
	if (stepId === "voice-clone") {
		return t("pipeline.steps.voiceClone");
	}

	if (stepId === "lip-sync") {
		return t("pipeline.steps.lipSync");
	}

	if (stepId === "render") {
		return t("pipeline.steps.render");
	}

	return t(`pipeline.steps.${stepId}`);
}

export function getVideoPipelineStepDescription(
	t: TranslateFn,
	stepId: VideoPipelineStepId,
): string {
	if (stepId === "transcript") {
		return t("pipeline.artifactJourney.transcriptDescription");
	}

	if (stepId === "translations") {
		return t("pipeline.artifactJourney.translationsDescription");
	}

	if (stepId === "audio") {
		return t("pipeline.artifactJourney.audioDescription");
	}

	if (stepId === "voice-clone") {
		return t("pipeline.artifactJourney.voiceCloneDescription");
	}

	if (stepId === "lip-sync") {
		return t("pipeline.artifactJourney.lipSyncDescription");
	}

	return t("pipeline.artifactJourney.renderDescription");
}
