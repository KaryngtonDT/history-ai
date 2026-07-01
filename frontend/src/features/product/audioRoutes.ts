export type AudioPipelineStepId = "transcript" | "translations";

export interface AudioPipelineStep {
	id: AudioPipelineStepId;
	path: (audioId: string) => string;
}

export const AUDIO_PIPELINE_STEPS: AudioPipelineStep[] = [
	{
		id: "transcript",
		path: (audioId) => `/audio/${audioId}/transcript`,
	},
	{
		id: "translations",
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

type TranslateFn = (
	key: string,
	params?: Record<string, string | number>,
) => string;

export function getAudioPipelineStepLabel(
	t: TranslateFn,
	stepId: AudioPipelineStepId,
): string {
	return t(`pipeline.steps.${stepId}`);
}
