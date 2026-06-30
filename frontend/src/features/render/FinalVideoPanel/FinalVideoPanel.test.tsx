import { render, screen } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { FinalVideoPanel } from "./FinalVideoPanel";

vi.mock("@/services/lipsync/LipSyncService", () => ({
	lipSyncService: {
		listLipSyncs: vi.fn().mockResolvedValue([
			{
				videoId: "550e8400-e29b-41d4-a716-446655440099",
				targetLanguage: "french",
			},
		]),
	},
}));

vi.mock("@/services/render/VideoRenderService", () => ({
	videoRenderService: {
		listRenders: vi.fn().mockResolvedValue([]),
		getRender: vi.fn().mockResolvedValue(null),
		generateRender: vi.fn().mockResolvedValue(undefined),
	},
}));

describe("FinalVideoPanel", () => {
	it("renders final render controls when lip sync is available", async () => {
		render(
			<MemoryRouter
				initialEntries={["/video/550e8400-e29b-41d4-a716-446655440099/render"]}
			>
				<Routes>
					<Route path="/video/:videoId/render" element={<FinalVideoPanel />} />
				</Routes>
			</MemoryRouter>,
		);

		expect(
			await screen.findByRole("button", { name: "Render Final Video" }),
		).toBeInTheDocument();
	});
});
