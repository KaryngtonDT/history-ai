import { render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { AIEngineSettings } from "@/features/ai/AIEngineSettings/AIEngineSettings";
import { aiEngineService } from "@/services/ai/AIEngineService";

describe("AIEngineSettings", () => {
	it("renders enabled providers and coming soon sections", async () => {
		vi.spyOn(aiEngineService, "listEngines").mockResolvedValue([
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
		]);

		render(<AIEngineSettings />);

		await waitFor(() => {
			expect(screen.getByText("Faster Whisper")).toBeInTheDocument();
		});

		expect(screen.getByText("Coming soon")).toBeInTheDocument();
		expect(screen.getByText("Speech")).toBeInTheDocument();
	});
});
