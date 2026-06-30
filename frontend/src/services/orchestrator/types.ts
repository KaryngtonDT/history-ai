export type ProcessingMode = "manual" | "automatic";

export type ProcessingStrategy =
	| "balanced"
	| "quality"
	| "speed"
	| "low_memory";

export interface PipelineRecommendationStage {
	stage: string;
	providerId: string;
}

export interface PipelineRecommendation {
	id: string;
	strategy: ProcessingStrategy;
	explanation: string;
	estimatedDurationSeconds: number;
	estimatedQuality: number;
	estimatedVramGb: number;
	stages: PipelineRecommendationStage[];
}

export interface VideoAnalysisInput {
	detectedLanguage?: string;
	durationSeconds?: number;
	resolution?: string;
	fps?: number;
	gpuAvailable?: boolean;
	estimatedVramGb?: number;
	strategy?: ProcessingStrategy;
}

export const PROCESSING_MODE_LABELS: Record<ProcessingMode, string> = {
	manual: "Manual",
	automatic: "Automatic (Recommended)",
};

export const PROCESSING_STRATEGY_LABELS: Record<ProcessingStrategy, string> = {
	balanced: "Balanced",
	quality: "Quality",
	speed: "Speed",
	low_memory: "Low Memory",
};
