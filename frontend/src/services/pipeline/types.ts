export type PipelineStageType =
	| "speech_to_text"
	| "translation"
	| "text_to_speech"
	| "voice_clone"
	| "lip_sync"
	| "video_render";

export interface PipelineStage {
	stage: PipelineStageType;
	providerId: string;
}

export interface PipelineConfiguration {
	id: string;
	version: number;
	createdAt: string;
	updatedAt: string;
	stages: PipelineStage[];
}

export const PIPELINE_STAGE_ORDER: PipelineStageType[] = [
	"speech_to_text",
	"translation",
	"text_to_speech",
	"voice_clone",
	"lip_sync",
	"video_render",
];

export const PIPELINE_STAGE_LABELS: Record<PipelineStageType, string> = {
	speech_to_text: "Speech-to-Text",
	translation: "Translation",
	text_to_speech: "Text-to-Speech",
	voice_clone: "Voice Clone",
	lip_sync: "Lip Sync",
	video_render: "Video Render",
};

export function mapPipelineStageType(value: string): PipelineStageType {
	if (PIPELINE_STAGE_ORDER.includes(value as PipelineStageType)) {
		return value as PipelineStageType;
	}

	return "speech_to_text";
}
