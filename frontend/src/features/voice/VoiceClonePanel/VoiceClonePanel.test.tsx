import { render, screen, waitFor } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { VoiceClonePanel } from "./VoiceClonePanel";

vi.mock("@/services/translation/TranslationService", () => ({
	translationService: {
		listTranslations: vi.fn().mockResolvedValue([
			{
				videoId: "550e8400-e29b-41d4-a716-446655440099",
				targetLanguage: "french",
			},
		]),
	},
}));

vi.mock("@/services/audio/AudioService", () => ({
	audioService: {
		listAudio: vi.fn().mockResolvedValue([
			{
				videoId: "550e8400-e29b-41d4-a716-446655440099",
				targetLanguage: "french",
			},
		]),
	},
}));

vi.mock("@/services/voice/VoiceCloneService", () => ({
	voiceCloneService: {
		listVoiceClones: vi.fn().mockResolvedValue([]),
		getVoiceClone: vi.fn().mockResolvedValue(null),
		generateVoiceClone: vi.fn().mockResolvedValue(undefined),
	},
}));

describe("VoiceClonePanel", () => {
	it("renders voice clone preview heading", async () => {
		render(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/voice-clone",
				]}
			>
				<Routes>
					<Route
						path="/video/:videoId/voice-clone"
						element={<VoiceClonePanel />}
					/>
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Voice Clone Preview")).toBeInTheDocument();
		});
	});

	it("shows voice mode selector when prerequisites exist", async () => {
		render(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/voice-clone",
				]}
			>
				<Routes>
					<Route
						path="/video/:videoId/voice-clone"
						element={<VoiceClonePanel />}
					/>
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Clone Original Voice")).toBeInTheDocument();
		});

		expect(screen.getByLabelText("Voice Clone Engine")).toBeInTheDocument();
	});
});
