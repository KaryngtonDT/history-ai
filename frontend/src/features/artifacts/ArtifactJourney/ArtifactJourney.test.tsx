import { screen } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { renderWithProviders } from "@/test/render";
import { ArtifactJourney } from "./ArtifactJourney";

describe("ArtifactJourney", () => {
	it("renders full pipeline when videoId is provided", () => {
		renderWithProviders(
			<MemoryRouter>
				<ArtifactJourney videoId="vid-123" />
			</MemoryRouter>,
		);

		expect(screen.getByText("Artifact journey")).toBeInTheDocument();
		expect(screen.getByText("Transcript")).toBeInTheDocument();
		expect(screen.getByText("Translations")).toBeInTheDocument();
		expect(screen.getByText("Voice Clone")).toBeInTheDocument();
		expect(screen.getByText("Final Render")).toBeInTheDocument();
		expect(
			screen.getAllByRole("link", { name: /Open →/ }).length,
		).toBeGreaterThan(0);
	});

	it("shows upload prompt when no video is selected", () => {
		renderWithProviders(
			<MemoryRouter>
				<ArtifactJourney videoId={null} />
			</MemoryRouter>,
		);

		expect(screen.getByRole("link", { name: /Start →/ })).toHaveAttribute(
			"href",
			"/video/upload",
		);
	});
});
