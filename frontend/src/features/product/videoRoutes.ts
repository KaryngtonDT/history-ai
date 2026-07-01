export type VideoPipelineStepId =
	| "transcript"
	| "translations"
	| "audio"
	| "voice-clone"
	| "lip-sync"
	| "render";

export interface VideoPipelineStep {
	id: VideoPipelineStepId;
	label: string;
	shortDescription: string;
	path: (videoId: string) => string;
	sprint: number;
	dependsOn?: VideoPipelineStepId;
}

export const VIDEO_PIPELINE_STEPS: VideoPipelineStep[] = [
	{
		id: "transcript",
		label: "Transcript",
		shortDescription: "Speech-to-text output from your video.",
		path: (videoId) => `/video/${videoId}/transcript`,
		sprint: 32,
	},
	{
		id: "translations",
		label: "Translations",
		shortDescription: "Translated text in your target languages.",
		path: (videoId) => `/video/${videoId}/translations`,
		sprint: 33,
		dependsOn: "transcript",
	},
	{
		id: "audio",
		label: "Audio",
		shortDescription: "Generated speech audio per language.",
		path: (videoId) => `/video/${videoId}/audio`,
		sprint: 35,
		dependsOn: "translations",
	},
	{
		id: "voice-clone",
		label: "Voice Clone",
		shortDescription: "Audio matched to the original speaker voice.",
		path: (videoId) => `/video/${videoId}/voice-clone`,
		sprint: 36,
		dependsOn: "audio",
	},
	{
		id: "lip-sync",
		label: "Lip Sync",
		shortDescription: "Video with lip movements aligned to new audio.",
		path: (videoId) => `/video/${videoId}/lip-sync`,
		sprint: 37,
		dependsOn: "voice-clone",
	},
	{
		id: "render",
		label: "Final Render",
		shortDescription: "Downloadable final MP4.",
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
