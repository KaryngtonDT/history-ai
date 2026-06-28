import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { SeeAlsoRecommendationsPanel } from "./SeeAlsoRecommendationsPanel";

const { mockGetArtifactRecommendations } = vi.hoisted(() => ({
	mockGetArtifactRecommendations: vi.fn(),
}));

vi.mock("@/services/recommendation/RecommendationService", () => ({
	recommendationService: {
		getArtifactRecommendations: mockGetArtifactRecommendations,
	},
}));

const contentId = "550e8400-e29b-41d4-a716-446655440000";
const artifactId = "550e8400-e29b-41d4-a716-446655440002";

describe("SeeAlsoRecommendationsPanel", () => {
	beforeEach(() => {
		mockGetArtifactRecommendations.mockReset();
	});

	it("calls RecommendationService with content and artifact ids", async () => {
		mockGetArtifactRecommendations.mockResolvedValue([]);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		await waitFor(() => {
			expect(mockGetArtifactRecommendations).toHaveBeenCalledWith(
				contentId,
				artifactId,
			);
		});
	});

	it("shows loading state while recommendations load", () => {
		mockGetArtifactRecommendations.mockReturnValue(new Promise(() => {}));

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(
			screen.getByRole("status", { name: "Loading recommendations" }),
		).toBeInTheDocument();
	});

	it("shows empty state when no recommendations are returned", async () => {
		mockGetArtifactRecommendations.mockResolvedValue([]);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(
			await screen.findByText("No recommendations yet"),
		).toBeInTheDocument();
	});

	it("shows error state when RecommendationService fails", async () => {
		mockGetArtifactRecommendations.mockRejectedValue(
			new Error("Network error"),
		);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(
			await screen.findByText("Unable to load recommendations"),
		).toBeInTheDocument();
	});

	it("renders recommendations with title, type, and reason labels", async () => {
		mockGetArtifactRecommendations.mockResolvedValue([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440003",
				type: "quiz",
				title: "Roman Empire Quiz",
				reason: "derived_from",
				score: 100,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				type: "timeline",
				title: "Roman Timeline",
				reason: "references",
				score: 80,
			},
		]);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(await screen.findByText("Roman Empire Quiz")).toBeInTheDocument();
		expect(screen.getByText("Roman Timeline")).toBeInTheDocument();
		expect(screen.getByText("Derived from")).toBeInTheDocument();
		expect(screen.getByText("References")).toBeInTheDocument();
		expect(screen.getAllByText("Quiz")).toHaveLength(1);
		expect(screen.getByText("Timeline")).toBeInTheDocument();
		expect(screen.getByText("100% relevant")).toBeInTheDocument();
		expect(screen.getByText("80% relevant")).toBeInTheDocument();
	});

	it("displays score relevance badges", async () => {
		mockGetArtifactRecommendations.mockResolvedValue([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440003",
				type: "quiz",
				title: "Practice Quiz",
				reason: "derived_from",
				score: 100,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				type: "timeline",
				title: "Event Timeline",
				reason: "references",
				score: 80,
			},
		]);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(await screen.findByText("100% relevant")).toBeInTheDocument();
		expect(screen.getByText("80% relevant")).toBeInTheDocument();
	});

	it("hides score badge when score is missing", async () => {
		mockGetArtifactRecommendations.mockResolvedValue([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440003",
				type: "quiz",
				title: "Legacy Quiz",
				reason: "related",
			},
		]);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(await screen.findByText("Legacy Quiz")).toBeInTheDocument();
		expect(screen.queryByText(/% relevant/)).not.toBeInTheDocument();
	});

	it("preserves backend recommendation order", async () => {
		mockGetArtifactRecommendations.mockResolvedValue([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440003",
				type: "quiz",
				title: "Higher score",
				reason: "derived_from",
				score: 100,
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				type: "timeline",
				title: "Lower score",
				reason: "references",
				score: 80,
			},
		]);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(await screen.findByText("Higher score")).toBeInTheDocument();
		const links = screen.getAllByRole("link");
		expect(links[0]).toHaveAccessibleName(/Higher score/);
		expect(links[1]).toHaveAccessibleName(/Lower score/);
	});

	it("links recommendations to artifact anchors", async () => {
		mockGetArtifactRecommendations.mockResolvedValue([
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440001",
				type: "summary",
				title: "Summary overview",
				reason: "related",
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440003",
				type: "quiz",
				title: "Practice quiz",
				reason: "next",
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440004",
				type: "flashcards",
				title: "Key terms",
				reason: "previous",
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440005",
				type: "timeline",
				title: "Event timeline",
				reason: "references",
			},
			{
				artifactId: "550e8400-e29b-41d4-a716-446655440006",
				type: "transcript",
				title: "Full transcript",
				reason: "derived_from",
			},
		]);

		render(
			<SeeAlsoRecommendationsPanel
				contentId={contentId}
				artifactId={artifactId}
			/>,
		);

		expect(await screen.findByText("Summary overview")).toBeInTheDocument();
		expect(
			screen.getByRole("link", { name: /Summary overview/ }),
		).toHaveAttribute("href", "#artifact-summary");
		expect(screen.getByRole("link", { name: /Practice quiz/ })).toHaveAttribute(
			"href",
			"#artifact-quiz",
		);
		expect(screen.getByRole("link", { name: /Key terms/ })).toHaveAttribute(
			"href",
			"#artifact-flashcards",
		);
		expect(
			screen.getByRole("link", { name: /Event timeline/ }),
		).toHaveAttribute("href", "#artifact-timeline");
		expect(
			screen.getByRole("link", { name: /Full transcript/ }),
		).toHaveAttribute("href", "#artifact-transcript");
	});

	it("does not use direct fetch or HTTP repository imports", () => {
		const source = readFileSync(
			join(__dirname, "SeeAlsoRecommendationsPanel.tsx"),
			"utf8",
		);
		const fetchPattern = ["fetch", "("].join("");

		expect(source).not.toContain(fetchPattern);
		expect(source).not.toContain("HttpRecommendationRepository");
	});
});
