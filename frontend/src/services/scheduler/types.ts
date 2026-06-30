export interface ResourceRequirement {
	type: string;
	weight: number;
}

export interface ScheduledStage {
	stage: string;
	order: number;
	status: string;
	estimatedDurationSeconds: number;
	parallelGroup: number;
	requirements: ResourceRequirement[];
}

export interface ExecutionResource {
	type: string;
	running: number;
	pending: number;
	maxConcurrency: number;
}

export interface ExecutionSchedule {
	id: string;
	videoId?: string;
	strategy: string;
	estimatedCompletionSeconds: number;
	currentStage?: string | null;
	currentResource?: string | null;
	stages: ScheduledStage[];
	resources: ExecutionResource[];
}

export const SCHEDULING_STRATEGY_LABELS: Record<string, string> = {
	balanced: "Balanced",
	quality: "Quality",
	speed: "Speed",
	low_memory: "Low Memory",
};

export const RESOURCE_TYPE_LABELS: Record<string, string> = {
	cpu: "CPU",
	gpu: "GPU",
	io: "IO",
};

export const STAGE_STATUS_LABELS: Record<string, string> = {
	pending: "Pending",
	running: "Running",
	completed: "Completed",
	failed: "Failed",
};

export const SCHEDULER_STAGE_LABELS: Record<string, string> = {
	speech_to_text: "Speech Recognition",
	translation: "Translation",
	text_to_speech: "Text To Speech",
	voice_clone: "Voice Clone",
	lip_sync: "Lip Sync",
	video_render: "Rendering",
};
