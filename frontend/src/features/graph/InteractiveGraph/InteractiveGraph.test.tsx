import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { InteractiveGraph } from "./InteractiveGraph";

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
		expect(screen.getByRole("link", { name: "Transcript" })).toHaveAttribute(
			"href",
			"#artifact-transcript",
		);
		expect(screen.getByRole("link", { name: "Summary" })).toHaveAttribute(
			"href",
			"#artifact-summary",
		);
		expect(screen.getByRole("link", { name: "Quiz" })).toHaveAttribute(
			"href",
			"#artifact-quiz",
		);
		expect(screen.getByText("Derived from")).toBeInTheDocument();
		expect(screen.getByText("References")).toBeInTheDocument();
	});

	it("preserves node and edge order", () => {
		render(<InteractiveGraph graph={graph} />);

		const nodeLinks = screen.getAllByRole("link");
		expect(nodeLinks.map((link) => link.textContent)).toEqual([
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
});
