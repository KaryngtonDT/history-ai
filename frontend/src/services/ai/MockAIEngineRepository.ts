import type { AIEngineRepository } from "./AIEngineRepository";
import type { AIEngine } from "./types";

export class MockAIEngineRepository implements AIEngineRepository {
	async listEngines(): Promise<AIEngine[]> {
		return [
			{
				engineId: "speech-to-text",
				capability: "speech_to_text",
				enabled: true,
				providers: [
					{
						providerId: "faster_whisper",
						displayName: "Faster Whisper",
						capability: "speech_to_text",
						enabled: true,
					},
				],
			},
			{
				engineId: "translation",
				capability: "translation",
				enabled: true,
				providers: [
					{
						providerId: "ollama",
						displayName: "Ollama",
						capability: "translation",
						enabled: true,
					},
				],
			},
			{
				engineId: "text-to-speech",
				capability: "text_to_speech",
				enabled: true,
				providers: [
					{
						providerId: "f5_tts",
						displayName: "F5-TTS",
						capability: "text_to_speech",
						enabled: false,
					},
				],
			},
			{
				engineId: "lip-sync",
				capability: "lip_sync",
				enabled: true,
				providers: [
					{
						providerId: "latentsync",
						displayName: "LatentSync",
						capability: "lip_sync",
						enabled: false,
					},
				],
			},
		];
	}
}
