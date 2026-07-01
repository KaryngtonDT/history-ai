import { screen } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { AnalyticsDashboard } from "@/features/analytics";
import { RoleSelector } from "@/features/collaboration/RoleSelector";
import { ReviewPanel } from "@/features/review";
import { WorkspacePage } from "@/features/workspace";
import { renderWithProviders } from "@/test/render";

describe("workspace i18n", () => {
	it("renders workspace page in French", async () => {
		renderWithProviders(
			<MemoryRouter>
				<WorkspacePage />
			</MemoryRouter>,
			{ locale: "fr" },
		);

		expect(await screen.findByText("Espace de projets")).toBeInTheDocument();
	});

	it("renders workspace page in German", async () => {
		renderWithProviders(
			<MemoryRouter>
				<WorkspacePage />
			</MemoryRouter>,
			{ locale: "de" },
		);

		expect(
			await screen.findByText("Projekt-Arbeitsbereich"),
		).toBeInTheDocument();
	});

	it("renders review form labels in French", () => {
		renderWithProviders(<ReviewPanel videoId="vid-1" />, {
			locale: "fr",
		});

		expect(screen.getByRole("heading", { name: "Avis" })).toBeInTheDocument();
		expect(screen.getByLabelText("Commentaire")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Enregistrer l'avis" }),
		).toBeInTheDocument();
	});

	it("renders analytics labels in German", () => {
		renderWithProviders(
			<AnalyticsDashboard
				analytics={{
					processedVideos: 12,
					averageProcessingTimeSeconds: 90,
					averageProcessingTimeLabel: "1m 30s",
					averageQuality: 95,
					successRate: 99,
					gpuUsagePercent: 71,
					topTranslationProvider: "Ollama",
					topTtsProvider: "F5-TTS",
					recentErrors: [],
				}}
			/>,
			{ locale: "de" },
		);

		expect(screen.getByText("Arbeitsbereich-Analytik")).toBeInTheDocument();
		expect(screen.getByText("Verarbeitete Videos")).toBeInTheDocument();
	});

	it("renders translated team role labels with enum values", () => {
		renderWithProviders(
			<RoleSelector
				value="editor"
				options={["owner", "editor", "reviewer", "viewer"]}
				onChange={() => {}}
			/>,
			{ locale: "fr" },
		);

		const ownerOption = screen.getByRole("option", { name: "Propriétaire" });
		const reviewerOption = screen.getByRole("option", { name: "Réviseur" });
		expect(ownerOption).toHaveAttribute("value", "owner");
		expect(reviewerOption).toHaveAttribute("value", "reviewer");
	});
});
