export type AudioPipelineStepId = "transcript" | "translations";

export interface AudioPipelineStep {
	id: AudioPipelineStepId;
	label: string;
	path: (audioId: string) => string;
}

export const AUDIO_PIPELINE_STEPS: AudioPipelineStep[] = [
	{
		id: "transcript",
		label: "Transcript",
		path: (audioId) => `/audio/${audioId}/transcript`,
	},
	{
		id: "translations",
		label: "Translations",
		path: (audioId) => `/audio/${audioId}/translations`,
	},
];

export function audioPipelinePath(
	stepId: AudioPipelineStepId,
	audioId: string,
): string {
	const step = AUDIO_PIPELINE_STEPS.find((entry) => entry.id === stepId);
	return step ? step.path(audioId) : `/audio/${audioId}`;
}
