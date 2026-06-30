export interface VideoSpeaker {
	index: number;
	label: string;
}

export interface VideoIntelligenceAudio {
	language: string;
	speakerCount: number;
	backgroundNoise: string;
	backgroundMusic: string;
	speechSpeed: string;
	confidence: number;
}

export interface VideoIntelligenceVisual {
	resolution: string;
	fps: number;
	lighting: string;
	lipVisibility: string;
	faceCount: number;
}

export interface VideoIntelligenceSpeech {
	dominantEmotion: string;
	averageSpeakingRate: number;
	pauseCount: number;
	hasOverlaps: boolean;
}

export interface VideoIntelligence {
	id: string;
	videoId?: string;
	durationSeconds: number;
	scene: string;
	audio: VideoIntelligenceAudio;
	visual: VideoIntelligenceVisual;
	speech: VideoIntelligenceSpeech;
	speakers: VideoSpeaker[];
	gpuAvailable: boolean;
	estimatedVramGb: number;
}

export const SCENE_LABELS: Record<string, string> = {
	interview: "Interview",
	presentation: "Presentation",
	podcast: "Podcast",
	conversation: "Conversation",
	lecture: "Lecture",
	other: "Other",
};

export const NOISE_LABELS: Record<string, string> = {
	none: "None",
	low: "Low",
	medium: "Medium",
	high: "High",
};

export const MUSIC_LABELS: Record<string, string> = {
	detected: "Detected",
	not_detected: "Not detected",
};

export const SPEECH_SPEED_LABELS: Record<string, string> = {
	slow: "Slow",
	normal: "Normal",
	fast: "Fast",
};

export const EMOTION_LABELS: Record<string, string> = {
	neutral: "Neutral",
	happy: "Happy",
	sad: "Sad",
	angry: "Angry",
	excited: "Excited",
};

export const LIGHTING_LABELS: Record<string, string> = {
	excellent: "Excellent",
	good: "Good",
	average: "Average",
	poor: "Poor",
};

export const LIP_VISIBILITY_LABELS: Record<string, string> = {
	excellent: "Excellent",
	good: "Good",
	partial: "Partial",
	poor: "Poor",
};
