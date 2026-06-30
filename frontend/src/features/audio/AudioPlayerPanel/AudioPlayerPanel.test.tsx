import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { AudioPlayerPanel } from "./AudioPlayerPanel";

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
		listAudio: vi.fn().mockResolvedValue([]),
		getAudio: vi.fn().mockResolvedValue(null),
		generateAudio: vi.fn().mockResolvedValue(undefined),
	},
}));

describe("AudioPlayerPanel", () => {
	it("renders generate controls when translations exist", async () => {
		render(
			<MemoryRouter
				initialEntries={["/video/550e8400-e29b-41d4-a716-446655440099/audio"]}
			>
				<Routes>
					<Route path="/video/:videoId/audio" element={<AudioPlayerPanel />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Generate Audio")).toBeInTheDocument();
		});

		expect(screen.getByLabelText("TTS engine")).toBeInTheDocument();
		expect(screen.getByLabelText("Voice")).toBeInTheDocument();
	});

	it("shows empty state when no audio exists", async () => {
		render(
			<MemoryRouter
				initialEntries={["/video/550e8400-e29b-41d4-a716-446655440099/audio"]}
			>
				<Routes>
					<Route path="/video/:videoId/audio" element={<AudioPlayerPanel />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("No audio yet")).toBeInTheDocument();
		});
	});

	it("calls generate on button click", async () => {
		const { audioService } = await import("@/services/audio/AudioService");
		const user = userEvent.setup();

		render(
			<MemoryRouter
				initialEntries={["/video/550e8400-e29b-41d4-a716-446655440099/audio"]}
			>
				<Routes>
					<Route path="/video/:videoId/audio" element={<AudioPlayerPanel />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Generate Audio")).toBeInTheDocument();
		});

		await user.click(screen.getByText("Generate Audio"));

		await waitFor(() => {
			expect(audioService.generateAudio).toHaveBeenCalled();
		});
	});
});
