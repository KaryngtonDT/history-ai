export interface OptimizationParameter {
	key: string;
	value: string;
}

export interface OptimizationStage {
	stage: string;
	parameters: OptimizationParameter[];
	explanations?: string[];
}

export interface ExecutionOptimization {
	id: string;
	videoId?: string;
	profile: string;
	summary: string;
	estimatedImpact: number;
	stages: OptimizationStage[];
	explanations: string[];
}

export const OPTIMIZATION_PROFILE_LABELS: Record<string, string> = {
	balanced: "Balanced",
	quality: "Quality",
	speed: "Speed",
	low_memory: "Low Memory",
};

export const OPTIMIZATION_STAGE_LABELS: Record<string, string> = {
	speech_to_text: "Speech Recognition",
	translation: "Translation",
	text_to_speech: "Text To Speech",
	voice_clone: "Voice Clone",
	lip_sync: "Lip Sync",
	video_render: "Rendering",
};

export const OPTIMIZATION_PARAMETER_LABELS: Record<string, string> = {
	beamSize: "Beam Size",
	chunkSize: "Chunk Size",
	temperature: "Temperature",
	style: "Style",
	pacing: "Pacing",
	stability: "Stability",
	strength: "Strength",
	preset: "Preset",
};
