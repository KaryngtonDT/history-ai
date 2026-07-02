import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { VoiceStudio } from "@/features/shadow/VoiceStudio";
import { I18nProvider } from "@/i18n";

vi.mock("@/services/shadowVoice/ShadowVoiceService", () => ({
	shadowVoiceService: {
		getLibrary: vi.fn().mockResolvedValue({
			engines: [{ id: "browser_tts", label: "Browser TTS", available: true }],
			voices: [
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
					preview: "Hello, I am Shadow.",
					collection: "friendly_companions",
					collectionLabel: "Friendly Companions",
					available: true,
				},
			],
		}),
		getCollections: vi.fn().mockResolvedValue({
			collections: [],
			presets: [{ id: "storyteller", label: "Storyteller" }],
		}),
		preview: vi.fn().mockResolvedValue({
			voiceId: "browser-default",
			engine: "browser_tts",
			text: "Hello, I am Shadow.",
			language: "en",
			parameters: {
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
		}),
		applyPreset: vi.fn(),
	},
}));

vi.mock("@/features/shadow/shadowVoice", () => ({
	speakShadowPreview: vi.fn(),
}));

describe("VoiceStudio", () => {
	it("renders voice studio sections", async () => {
		render(
			<I18nProvider initialLocale="en">
				<VoiceStudio />
			</I18nProvider>,
		);

		await waitFor(() => {
			expect(screen.getByText("Voice Studio")).toBeInTheDocument();
		});

		expect(
			screen.getByRole("button", { name: "Preview Voice" }),
		).toBeInTheDocument();
	});

	it("previews selected voice", async () => {
		const user = userEvent.setup();

		render(
			<I18nProvider initialLocale="en">
				<VoiceStudio />
			</I18nProvider>,
		);

		await waitFor(() => {
			expect(screen.getByText("Voice Studio")).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Preview Voice" }));

		const { shadowVoiceService } = await import(
			"@/services/shadowVoice/ShadowVoiceService"
		);
		expect(shadowVoiceService.preview).toHaveBeenCalled();
	});
});
