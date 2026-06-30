export type AIEngineCapability =
	| "speech_to_text"
	| "translation"
	| "text_to_speech"
	| "voice_clone"
	| "lip_sync";

export interface AIProvider {
	providerId: string;
	displayName: string;
	capability: AIEngineCapability;
	enabled: boolean;
}

export interface AIEngine {
	engineId: string;
	capability: AIEngineCapability;
	enabled: boolean;
	providers: AIProvider[];
}

export interface AIProvidersResponse {
	engines: AIEngine[];
}

export const AI_CAPABILITY_LABELS: Record<AIEngineCapability, string> = {
	speech_to_text: "Speech",
	translation: "Translation",
	text_to_speech: "Voice",
	voice_clone: "Voice Clone",
	lip_sync: "Lip Sync",
};

export function mapAIEngineCapability(value: string): AIEngineCapability {
	switch (value) {
		case "speech_to_text":
		case "translation":
		case "text_to_speech":
		case "voice_clone":
		case "lip_sync":
			return value;
		default:
			return "speech_to_text";
	}
}
