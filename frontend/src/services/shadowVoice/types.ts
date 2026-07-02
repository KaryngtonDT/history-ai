export interface ShadowVoiceEngineOption {
	id: string;
	label: string;
	available: boolean;
}

export interface ShadowVoiceDefinition {
	id: string;
	name: string;
	engine: string;
	engineLabel: string;
	supportedLanguages: string[];
	gender: string;
	accent: string;
	quality: string;
	latency: string;
	preview: string;
	collection: string;
	collectionLabel: string;
	available: boolean;
}

export interface ShadowVoiceLibraryResponse {
	engines: ShadowVoiceEngineOption[];
	voices: ShadowVoiceDefinition[];
}

export interface ShadowVoiceCollection {
	id: string;
	label: string;
	description: string;
	voiceIds: string[];
}

export interface ShadowVoicePreset {
	id: string;
	label: string;
}

export interface ShadowVoiceCollectionsResponse {
	collections: ShadowVoiceCollection[];
	presets: ShadowVoicePreset[];
}

export interface ShadowVoicePreviewRequest {
	voiceId: string;
	parameters?: {
		speed?: number;
		pitch?: number;
		warmth?: number;
		energy?: number;
		emotion?: number;
		pauses?: number;
		expressiveness?: number;
		humor?: string;
	};
}

export interface ShadowVoicePreviewResponse {
	voiceId: string;
	engine: string;
	text: string;
	language: string;
	parameters: {
		speed: number;
		pitch: number;
		warmth: number;
		energy: number;
		emotion: number;
		pauses: number;
		expressiveness: number;
		thinkingPauses: boolean;
		humor: string;
	};
}

export interface ShadowVoicePresetResponse {
	preset: string;
	persona: string;
	voiceProfile: {
		voiceId: string;
		engine: string;
		speed: number;
		pitch: number;
		warmth: number;
		energy: number;
		emotion: number;
		pauses: number;
		expressiveness: number;
		thinkingPauses: boolean;
		humor: string;
	};
}

export interface VoiceStudioParameters {
	speed: number;
	pitch: number;
	warmth: number;
	energy: number;
	emotion: number;
	pauses: number;
	expressiveness: number;
	humor: string;
}
