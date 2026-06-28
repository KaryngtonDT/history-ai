import { render, screen, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import { ArtifactRelationsPanel } from "./ArtifactRelationsPanel";

const { mockGetArtifactRelations } = vi.hoisted(() => ({
	mockGetArtifactRelations: vi.fn(),
}));

vi.mock("@/services/relation/RelationService", () => ({
	relationService: {
		getArtifactRelations: mockGetArtifactRelations,
	},
}));

const artifacts: Artifact[] = [
	{
		id: "550e8400-e29b-41d4-a716-446655440001",
		contentId: "550e8400-e29b-41d4-a716-446655440000",
		processingJobId: "job-1",
		type: "transcript",
		content: "Transcript text",
		createdAt: "2026-06-26T12:00:00+00:00",
	},
	{
		id: "550e8400-e29b-41d4-a716-446655440002",
		contentId: "550e8400-e29b-41d4-a716-446655440000",
		processingJobId: "job-1",
		type: "summary",
		content: "Summary text",
		createdAt: "2026-06-26T12:00:01+00:00",
	},
	{
		id: "550e8400-e29b-41d4-a716-446655440003",
		contentId: "550e8400-e29b-41d4-a716-446655440000",
		processingJobId: "job-1",
		type: "quiz",
		content: "Quiz text",
		createdAt: "2026-06-26T12:00:02+00:00",
	},
];

describe("ArtifactRelationsPanel", () => {
	beforeEach(() => {
		mockGetArtifactRelations.mockReset();
	});

	it("calls RelationService with content id", async () => {
		mockGetArtifactRelations.mockResolvedValue([]);

		render(
			<ArtifactRelationsPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				artifacts={artifacts}
			/>,
		);

		await waitFor(() => {
			expect(mockGetArtifactRelations).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
			);
		});
	});

	it("shows loading state while relations load", () => {
		mockGetArtifactRelations.mockReturnValue(new Promise(() => {}));

		render(
			<ArtifactRelationsPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				artifacts={artifacts}
			/>,
		);

		expect(
			screen.getByRole("status", { name: "Loading artifact relations" }),
		).toBeInTheDocument();
	});

	it("shows empty state when no relations are returned", async () => {
		mockGetArtifactRelations.mockResolvedValue([]);

		render(
			<ArtifactRelationsPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				artifacts={artifacts}
			/>,
		);

		expect(await screen.findByText("No relations yet")).toBeInTheDocument();
	});

	it("shows error state when RelationService fails", async () => {
		mockGetArtifactRelations.mockRejectedValue(new Error("Network error"));

		render(
			<ArtifactRelationsPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				artifacts={artifacts}
			/>,
		);

		expect(
			await screen.findByText("Unable to load relations"),
		).toBeInTheDocument();
	});

	it("renders relation rows with artifact and relation labels", async () => {
		mockGetArtifactRelations.mockResolvedValue([
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
				type: "derived_from",
			},
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440003",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440002",
				type: "references",
			},
		]);

		render(
			<ArtifactRelationsPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				artifacts={artifacts}
			/>,
		);

		expect(await screen.findByText("Derived from")).toBeInTheDocument();
		expect(screen.getByText("References")).toBeInTheDocument();
		expect(screen.getAllByRole("link", { name: "Summary" })).toHaveLength(2);
		expect(screen.getByRole("link", { name: "Transcript" })).toHaveAttribute(
			"href",
			"#artifact-transcript",
		);
		expect(screen.getByRole("link", { name: "Quiz" })).toHaveAttribute(
			"href",
			"#artifact-quiz",
		);
	});
});
