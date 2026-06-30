import { render, screen } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { LipSyncPanel } from "./LipSyncPanel";

vi.mock("@/services/voice/VoiceCloneService", () => ({
	voiceCloneService: {
		listVoiceClones: vi.fn().mockResolvedValue([
			{
				targetLanguage: "french",
			},
		]),
	},
}));

vi.mock("@/services/lipsync/LipSyncService", () => ({
	lipSyncService: {
		listLipSyncs: vi.fn().mockResolvedValue([]),
		getLipSync: vi.fn().mockResolvedValue(null),
		generateLipSync: vi.fn().mockResolvedValue(undefined),
	},
}));

describe("LipSyncPanel", () => {
	it("renders lip sync controls when voice clone exists", async () => {
		render(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/lip-sync",
				]}
			>
				<Routes>
					<Route path="/video/:videoId/lip-sync" element={<LipSyncPanel />} />
				</Routes>
			</MemoryRouter>,
		);

		expect(await screen.findByText("Lip Sync")).toBeInTheDocument();
		expect(screen.getByText("Generate Lip Sync")).toBeInTheDocument();
	});
});
