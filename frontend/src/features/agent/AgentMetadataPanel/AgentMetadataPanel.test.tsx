import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import type { AgentExecution } from "@/services/agent/types";
import { AgentMetadataPanel } from "./AgentMetadataPanel";

const execution: AgentExecution = {
	plan: [],
	steps: [
		{
			order: 0,
			tool: "semantic_search",
			status: "completed",
			summary: "Semantic search found 4 relevant chunks.",
			metadata: { resultCount: 4, topScore: 0.92 },
		},
		{
			order: 1,
			tool: "knowledge_graph",
			status: "completed",
			summary: "Knowledge graph contains 12 nodes and 18 relationships.",
			metadata: { nodeCount: 12, edgeCount: 18 },
		},
	],
	finalSummary: "Agent workflow completed.",
	metadata: {
		resultCount: 4,
		topScore: 0.92,
		nodeCount: 12,
		edgeCount: 18,
	},
};

describe("AgentMetadataPanel", () => {
	it("renders metadata sections for executed tools", () => {
		render(<AgentMetadataPanel execution={execution} />);

		expect(
			screen.getByRole("region", { name: "Agent metadata" }),
		).toBeInTheDocument();
		expect(screen.getByText("Metadata")).toBeInTheDocument();
		expect(
			screen.getByLabelText("Semantic Search metadata"),
		).toBeInTheDocument();
		expect(
			screen.getByLabelText("Knowledge Graph metadata"),
		).toBeInTheDocument();
		expect(screen.getByText("Chunks")).toBeInTheDocument();
		expect(screen.getByText("4")).toBeInTheDocument();
		expect(screen.getByText("Top score")).toBeInTheDocument();
		expect(screen.getByText("0.92")).toBeInTheDocument();
		expect(screen.getByText("Nodes")).toBeInTheDocument();
		expect(screen.getByText("12")).toBeInTheDocument();
		expect(screen.getByText("Edges")).toBeInTheDocument();
		expect(screen.getByText("18")).toBeInTheDocument();
	});

	it("renders nothing when no metadata is available", () => {
		const { container } = render(
			<AgentMetadataPanel
				execution={{
					plan: [],
					steps: [
						{
							order: 0,
							tool: "semantic_search",
							status: "completed",
							summary: "Semantic search found no relevant chunks.",
							metadata: {},
						},
					],
					finalSummary: "Agent workflow completed.",
					metadata: {},
				}}
			/>,
		);

		expect(container).toBeEmptyDOMElement();
	});
});
