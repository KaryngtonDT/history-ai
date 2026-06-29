import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { KnowledgeGraphPanel } from "./KnowledgeGraphPanel";

const {
	mockGetKnowledgeGraph,
	mockGetGraphNeighborhood,
	mockGetConversationGraph,
} = vi.hoisted(() => ({
	mockGetKnowledgeGraph: vi.fn(),
	mockGetGraphNeighborhood: vi.fn(),
	mockGetConversationGraph: vi.fn(),
}));

vi.mock("@/services/graph/GraphService", () => ({
	graphService: {
		getKnowledgeGraph: mockGetKnowledgeGraph,
		getGraphNeighborhood: mockGetGraphNeighborhood,
		getConversationGraph: mockGetConversationGraph,
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
		{
			artifactId: "550e8400-e29b-41d4-a716-446655440003",
			type: "quiz" as const,
			title: "Quiz",
		},
	],
	edges: [
		{
			sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
			targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "derived_from" as const,
		},
		{
			sourceArtifactId: "550e8400-e29b-41d4-a716-446655440003",
			targetArtifactId: "550e8400-e29b-41d4-a716-446655440002",
			type: "references" as const,
		},
	],
};

const neighborhood = {
	center: graph.nodes[1],
	neighbors: [graph.nodes[0], graph.nodes[2]],
	edges: graph.edges,
};

describe("KnowledgeGraphPanel", () => {
	beforeEach(() => {
		mockGetKnowledgeGraph.mockReset();
		mockGetGraphNeighborhood.mockReset();
		mockGetConversationGraph.mockReset();
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
		expect(mockGetConversationGraph).not.toHaveBeenCalled();
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

	it("loads neighborhood when a node is selected", async () => {
		const user = userEvent.setup();
		mockGetKnowledgeGraph.mockResolvedValue(graph);
		mockGetGraphNeighborhood.mockResolvedValue(neighborhood);

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await screen.findByRole("region", { name: "Knowledge graph" });
		await user.click(
			screen.getByTestId(
				"graph-node-select-550e8400-e29b-41d4-a716-446655440002",
			),
		);

		await waitFor(() => {
			expect(mockGetGraphNeighborhood).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
				"550e8400-e29b-41d4-a716-446655440002",
			);
		});
	});

	it("shows neighborhood loading state while neighborhood loads", async () => {
		const user = userEvent.setup();
		mockGetKnowledgeGraph.mockResolvedValue(graph);
		mockGetGraphNeighborhood.mockReturnValue(new Promise(() => {}));

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await screen.findByRole("region", { name: "Knowledge graph" });
		await user.click(
			screen.getByTestId(
				"graph-node-select-550e8400-e29b-41d4-a716-446655440002",
			),
		);

		expect(
			screen.getByRole("status", { name: "Loading neighborhood" }),
		).toBeInTheDocument();
	});

	it("shows message when neighborhood is not found", async () => {
		const user = userEvent.setup();
		mockGetKnowledgeGraph.mockResolvedValue(graph);
		mockGetGraphNeighborhood.mockResolvedValue(null);

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await screen.findByRole("region", { name: "Knowledge graph" });
		await user.click(
			screen.getByTestId(
				"graph-node-select-550e8400-e29b-41d4-a716-446655440002",
			),
		);

		expect(
			await screen.findByText("Selected artifact is not part of this graph."),
		).toBeInTheDocument();
	});

	it("shows message when selected artifact has no neighbors", async () => {
		const user = userEvent.setup();
		mockGetKnowledgeGraph.mockResolvedValue(graph);
		mockGetGraphNeighborhood.mockResolvedValue({
			center: graph.nodes[0],
			neighbors: [],
			edges: [],
		});

		render(
			<KnowledgeGraphPanel contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await screen.findByRole("region", { name: "Knowledge graph" });
		await user.click(
			screen.getByTestId(
				"graph-node-select-550e8400-e29b-41d4-a716-446655440001",
			),
		);

		expect(
			await screen.findByText("No direct neighbors for the selected artifact."),
		).toBeInTheDocument();
	});

	it("uses conversation graph when conversation id is provided", async () => {
		mockGetConversationGraph.mockResolvedValue(graph);

		render(
			<KnowledgeGraphPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				conversationId="550e8400-e29b-41d4-a716-446655440001"
			/>,
		);

		await waitFor(() => {
			expect(mockGetConversationGraph).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440001",
			);
		});
		expect(mockGetKnowledgeGraph).not.toHaveBeenCalled();
		expect(
			await screen.findByRole("region", { name: "Knowledge graph" }),
		).toBeInTheDocument();
	});

	it("reloads conversation graph when conversation id changes", async () => {
		const conversationGraph = {
			nodes: [graph.nodes[0]],
			edges: [],
		};
		mockGetConversationGraph.mockResolvedValue(conversationGraph);

		const { rerender } = render(
			<KnowledgeGraphPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				conversationId="550e8400-e29b-41d4-a716-446655440001"
			/>,
		);

		await waitFor(() => {
			expect(mockGetConversationGraph).toHaveBeenCalledTimes(1);
		});

		rerender(
			<KnowledgeGraphPanel
				contentId="550e8400-e29b-41d4-a716-446655440000"
				conversationId="550e8400-e29b-41d4-a716-446655440002"
			/>,
		);

		await waitFor(() => {
			expect(mockGetConversationGraph).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440002",
			);
		});
		expect(mockGetConversationGraph).toHaveBeenCalledTimes(2);
	});
});
