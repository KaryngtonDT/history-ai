import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { PipelineBuilder } from "./PipelineBuilder";

vi.mock("@/services/ai/AIEngineService", () => ({
	aiEngineService: {
		listEngines: vi.fn().mockResolvedValue([
			{
				engineId: "speech-to-text",
				capability: "speech_to_text",
				enabled: true,
				providers: [
					{
						providerId: "faster_whisper",
						displayName: "FasterWhisper",
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
						enabled: true,
					},
				],
			},
			{
				engineId: "voice-clone",
				capability: "voice_clone",
				enabled: true,
				providers: [
					{
						providerId: "openvoice",
						displayName: "OpenVoice V2",
						capability: "voice_clone",
						enabled: true,
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
						enabled: true,
					},
				],
			},
			{
				engineId: "video-render",
				capability: "video_render",
				enabled: true,
				providers: [
					{
						providerId: "ffmpeg",
						displayName: "FFmpeg",
						capability: "video_render",
						enabled: true,
					},
				],
			},
		]),
	},
}));

vi.mock("@/services/pipeline/PipelineService", () => ({
	pipelineService: {
		loadConfiguration: vi.fn().mockResolvedValue({
			id: "550e8400-e29b-41d4-a716-446655440010",
			version: 1,
			createdAt: "",
			updatedAt: "",
			stages: [
				{ stage: "speech_to_text", providerId: "faster_whisper" },
				{ stage: "translation", providerId: "ollama" },
				{ stage: "text_to_speech", providerId: "f5_tts" },
				{ stage: "voice_clone", providerId: "openvoice" },
				{ stage: "lip_sync", providerId: "latentsync" },
				{ stage: "video_render", providerId: "ffmpeg" },
			],
		}),
		saveConfiguration: vi.fn().mockResolvedValue({}),
		resetConfiguration: vi.fn().mockResolvedValue({}),
	},
}));

describe("PipelineBuilder", () => {
	it("renders pipeline stage selectors", async () => {
		render(<PipelineBuilder />);

		expect(await screen.findByText("Processing Pipeline")).toBeInTheDocument();
		expect(screen.getByLabelText("Speech-to-Text")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Save Configuration" }),
		).toBeInTheDocument();
	});
});
