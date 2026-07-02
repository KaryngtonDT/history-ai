import type { ShadowVoiceRepository } from "./ShadowVoiceRepository";
import type {
	ShadowVoiceCollectionsResponse,
	ShadowVoiceLibraryResponse,
	ShadowVoicePresetResponse,
	ShadowVoicePreviewRequest,
	ShadowVoicePreviewResponse,
} from "./types";

const MOCK_VOICES: ShadowVoiceLibraryResponse = {
	engines: [
		{ id: "browser_tts", label: "Browser TTS", available: true },
		{ id: "f5_tts", label: "F5-TTS", available: false },
		{ id: "xtts", label: "XTTS", available: false },
		{ id: "openvoice", label: "OpenVoice", available: false },
	],
	voices: [
		{
			id: "storyteller-warm-en",
			name: "Warm Storyteller",
			engine: "browser_tts",
			engineLabel: "Browser TTS",
			supportedLanguages: ["en"],
			gender: "female",
			accent: "American",
			quality: "high",
			latency: "low",
			preview:
				"Let me tell you a story about how ideas travel across centuries.",
			collection: "great_storytellers",
			collectionLabel: "Great Storytellers",
			available: true,
		},
		{
			id: "browser-default",
			name: "Browser Default",
			engine: "browser_tts",
			engineLabel: "Browser TTS",
			supportedLanguages: ["en"],
			gender: "neutral",
			accent: "System",
			quality: "medium",
			latency: "low",
			preview: "Hello, I am Shadow. This is a voice preview.",
			collection: "friendly_companions",
			collectionLabel: "Friendly Companions",
			available: true,
		},
	],
};

const MOCK_COLLECTIONS: ShadowVoiceCollectionsResponse = {
	collections: [
		{
			id: "great_storytellers",
			label: "Great Storytellers",
			description:
				"Warm narrative voices for storytelling and history content.",
			voiceIds: ["storyteller-warm-en"],
		},
	],
	presets: [
		{ id: "storyteller", label: "Storyteller" },
		{ id: "professor", label: "Professor" },
		{ id: "custom", label: "Custom" },
	],
};

export class MockShadowVoiceRepository implements ShadowVoiceRepository {
	getLibrary(): Promise<ShadowVoiceLibraryResponse> {
		return Promise.resolve(MOCK_VOICES);
	}

	getCollections(): Promise<ShadowVoiceCollectionsResponse> {
		return Promise.resolve(MOCK_COLLECTIONS);
	}

	preview(
		request: ShadowVoicePreviewRequest,
	): Promise<ShadowVoicePreviewResponse> {
		const voice =
			MOCK_VOICES.voices.find((item) => item.id === request.voiceId) ??
			MOCK_VOICES.voices[0];

		return Promise.resolve({
			voiceId: voice.id,
			engine: voice.engine,
			text: voice.preview,
			language: voice.supportedLanguages[0] ?? "en",
			parameters: {
				speed: request.parameters?.speed ?? 1,
				pitch: request.parameters?.pitch ?? 1,
				warmth: request.parameters?.warmth ?? 6,
				energy: request.parameters?.energy ?? 6,
				emotion: request.parameters?.emotion ?? 5,
				pauses: request.parameters?.pauses ?? 5,
				expressiveness: request.parameters?.expressiveness ?? 6,
				thinkingPauses: true,
				humor: request.parameters?.humor ?? "low",
			},
		});
	}

	applyPreset(preset: string): Promise<ShadowVoicePresetResponse> {
		return Promise.resolve({
			preset,
			persona: preset === "storyteller" ? "storyteller" : "teacher",
			voiceProfile: {
				voiceId:
					preset === "storyteller" ? "storyteller-warm-en" : "browser-default",
				engine: "browser_tts",
				speed: 1,
				pitch: 1,
				warmth: 6,
				energy: 6,
				emotion: 5,
				pauses: 5,
				expressiveness: 6,
				thinkingPauses: true,
				humor: "low",
			},
		});
	}
}
