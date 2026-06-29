import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { buildGraphEdgeKey, InteractiveGraph } from "./InteractiveGraph";
import styles from "./InteractiveGraph.module.css";

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

describe("InteractiveGraph", () => {
	it("renders nodes and edges with titles and relation labels", () => {
		render(<InteractiveGraph graph={graph} />);

		expect(
			screen.getByRole("region", { name: "Knowledge graph" }),
		).toBeInTheDocument();
		expect(screen.getAllByRole("link", { name: "View artifact" })).toHaveLength(
			3,
		);
		expect(screen.getByText("Derived from")).toBeInTheDocument();
		expect(screen.getByText("References")).toBeInTheDocument();
	});

	it("preserves node and edge order", () => {
		render(<InteractiveGraph graph={graph} />);

		const chipButtons = [
			screen.getByTestId(
				"graph-node-chip-550e8400-e29b-41d4-a716-446655440001",
			),
			screen.getByTestId(
				"graph-node-chip-550e8400-e29b-41d4-a716-446655440002",
			),
			screen.getByTestId(
				"graph-node-chip-550e8400-e29b-41d4-a716-446655440003",
			),
		];
		expect(chipButtons.map((button) => button.textContent)).toEqual([
			"Transcript",
			"Summary",
			"Quiz",
		]);

		const edgeItems = screen.getAllByText(/Derived from|References/);
		expect(edgeItems.map((item) => item.textContent)).toEqual([
			"Derived from",
			"References",
		]);
	});

	it("calls onNodeSelect when a node is clicked", async () => {
		const user = userEvent.setup();
		const onNodeSelect = vi.fn();

		render(<InteractiveGraph graph={graph} onNodeSelect={onNodeSelect} />);

		await user.click(
			screen.getByTestId(
				"graph-node-select-550e8400-e29b-41d4-a716-446655440002",
			),
		);

		expect(onNodeSelect).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440002",
		);
	});

	it("renders selected, neighbor, and highlighted edge classes", () => {
		const selectedArtifactId = "550e8400-e29b-41d4-a716-446655440002";
		const neighborArtifactIds = new Set([
			"550e8400-e29b-41d4-a716-446655440001",
			"550e8400-e29b-41d4-a716-446655440003",
		]);
		const highlightedEdgeKeys = new Set([
			buildGraphEdgeKey(
				"550e8400-e29b-41d4-a716-446655440002",
				"550e8400-e29b-41d4-a716-446655440001",
				"derived_from",
			),
			buildGraphEdgeKey(
				"550e8400-e29b-41d4-a716-446655440003",
				"550e8400-e29b-41d4-a716-446655440002",
				"references",
			),
		]);

		render(
			<InteractiveGraph
				graph={graph}
				selectedArtifactId={selectedArtifactId}
				neighborArtifactIds={neighborArtifactIds}
				highlightedEdgeKeys={highlightedEdgeKeys}
			/>,
		);

		expect(
			screen.getByTestId(`graph-node-chip-${selectedArtifactId}`),
		).toHaveClass(styles.nodeChipSelected);
		expect(
			screen.getByTestId(
				"graph-node-chip-550e8400-e29b-41d4-a716-446655440001",
			),
		).toHaveClass(styles.nodeChipNeighbor);
		expect(
			screen.getByTestId(
				`graph-edge-${buildGraphEdgeKey(
					"550e8400-e29b-41d4-a716-446655440002",
					"550e8400-e29b-41d4-a716-446655440001",
					"derived_from",
				)}`,
			),
		).toHaveClass(styles.edgeItemHighlighted);
	});
});
