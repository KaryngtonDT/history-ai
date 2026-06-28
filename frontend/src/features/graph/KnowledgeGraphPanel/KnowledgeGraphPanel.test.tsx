import { render, screen, waitFor } from "@testing-library/react";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { KnowledgeGraphPanel } from "./KnowledgeGraphPanel";

const { mockGetKnowledgeGraph } = vi.hoisted(() => ({
	mockGetKnowledgeGraph: vi.fn(),
}));

vi.mock("@/services/graph/GraphService", () => ({
	graphService: {
		getKnowledgeGraph: mockGetKnowledgeGraph,
	},
}));

const graph = {
	nodes: [
		{
			artifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "transcript" as const,
			title: "Transcript",
		},
		{
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			type: "summary" as const,
			title: "Summary",
		},
	],
	edges: [
		{
			sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
			targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "derived_from" as const,
		},
	],
};

describe("KnowledgeGraphPanel", () => {
	beforeEach(() => {
		mockGetKnowledgeGraph.mockReset();
	});

	it("calls GraphService with content id", async () => {
		mockGetKnowledgeGraph.mockResolvedValue({ nodes: [], edges: [] });

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await waitFor(() => {
			expect(mockGetKnowledgeGraph).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
			);
		});
	});

	it("shows loading state while graph loads", () => {
		mockGetKnowledgeGraph.mockReturnValue(new Promise(() => {}));

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(
			screen.getByRole("status", { name: "Loading knowledge graph" }),
		).toBeInTheDocument();
	});

	it("renders InteractiveGraph when graph has nodes", async () => {
		mockGetKnowledgeGraph.mockResolvedValue(graph);

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(
			await screen.findByRole("region", { name: "Knowledge graph" }),
		).toBeInTheDocument();
		expect(screen.getByRole("link", { name: "Summary" })).toBeInTheDocument();
		expect(screen.getByText("Derived from")).toBeInTheDocument();
	});

	it("shows empty state when graph has no nodes", async () => {
		mockGetKnowledgeGraph.mockResolvedValue({ nodes: [], edges: [] });

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("No graph yet")).toBeInTheDocument();
	});

	it("shows error state when GraphService fails", async () => {
		mockGetKnowledgeGraph.mockRejectedValue(new Error("Network error"));

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("Unable to load graph")).toBeInTheDocument();
	});
});
