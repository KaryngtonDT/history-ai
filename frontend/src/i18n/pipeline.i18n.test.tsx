import { screen } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it } from "vitest";
import { VideoOverview } from "@/features/video/VideoOverview/VideoOverview";
import { VideoUploadPanel } from "@/features/video/VideoUploadPanel/VideoUploadPanel";
import { YouTubeImportPanel } from "@/features/youtube/YouTubeImportPanel/YouTubeImportPanel";
import { renderWithProviders } from "@/test/render";

describe("pipeline i18n", () => {
	it("renders upload CTA in English", () => {
		renderWithProviders(
			<MemoryRouter>
				<VideoUploadPanel />
			</MemoryRouter>,
			{ locale: "en" },
		);

		expect(
			screen.getByRole("button", { name: "Select video" }),
		).toBeInTheDocument();
	});

	it("renders upload CTA in French", () => {
		renderWithProviders(
			<MemoryRouter>
				<VideoUploadPanel />
			</MemoryRouter>,
			{ locale: "fr" },
		);

		expect(
			screen.getByRole("button", { name: "Sélectionner une vidéo" }),
		).toBeInTheDocument();
	});

	it("renders upload CTA in German", () => {
		renderWithProviders(
			<MemoryRouter>
				<VideoUploadPanel />
			</MemoryRouter>,
			{ locale: "de" },
		);

		expect(
			screen.getByRole("button", { name: "Video auswählen" }),
		).toBeInTheDocument();
	});

	it("renders YouTube import title in French", () => {
		renderWithProviders(
			<MemoryRouter>
				<YouTubeImportPanel />
			</MemoryRouter>,
			{ locale: "fr" },
		);

		expect(screen.getByText("Importer depuis YouTube")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Importer la vidéo" }),
		).toBeInTheDocument();
	});

	it("renders video overview in German", async () => {
		renderWithProviders(
			<MemoryRouter initialEntries={["/video/vid-123"]}>
				<Routes>
					<Route path="/video/:videoId" element={<VideoOverview />} />
				</Routes>
			</MemoryRouter>,
			{ locale: "de" },
		);

		expect(
			await screen.findByText(
				"Zentrale Übersicht für die Lokalisierungs-Pipeline dieses Videos.",
			),
		).toBeInTheDocument();
		expect(screen.getByRole("link", { name: "Transkript" })).toHaveAttribute(
			"href",
			"/video/vid-123/transcript",
		);
	});
});
