import { screen } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it } from "vitest";
import { renderWithProviders } from "@/test/render";
import { VideoOverview } from "./VideoOverview";

describe("VideoOverview", () => {
	it("renders overview hub with pipeline tabs", async () => {
		renderWithProviders(
			<MemoryRouter initialEntries={["/video/vid-123"]}>
				<Routes>
					<Route path="/video/:videoId" element={<VideoOverview />} />
				</Routes>
			</MemoryRouter>,
		);

		expect(
			await screen.findByText(
				"Central hub for this video's localization pipeline.",
			),
		).toBeInTheDocument();
		expect(screen.getByRole("link", { name: "Transcript" })).toHaveAttribute(
			"href",
			"/video/vid-123/transcript",
		);
		expect(
			screen.getByRole("region", { name: "Pipeline progress" }),
		).toBeInTheDocument();
		expect(
			screen.getByText(
				"You can safely leave this page. Refreshing will not restart background jobs.",
			),
		).toBeInTheDocument();
	});
});
