import { describe, expect, it } from "vitest";
import { MockGraphRepository } from "./MockGraphRepository";
import { EMPTY_KNOWLEDGE_GRAPH } from "./types";

describe("MockGraphRepository", () => {
	it("returns graph nodes and derived_from edge for mock transcript and summary", async () => {
		const repository = new MockGraphRepository();

		const graph = await repository.getKnowledgeGraph("1");

		expect(graph.nodes).toEqual([
			{
				artifactId: "artifact-transcript-1",
				type: "transcript",
				title: "Transcript",
			},
			{
				artifactId: "artifact-summary-1",
				type: "summary",
				title: "Summary",
			},
		]);
		expect(graph.edges).toContainEqual({
			sourceArtifactId: "artifact-summary-1",
			targetArtifactId: "artifact-transcript-1",
			type: "derived_from",
		});
	});

	it("returns empty graph when mock content has no artifacts", async () => {
		const repository = new MockGraphRepository();

		const graph = await repository.getKnowledgeGraph("missing-content");

		expect(graph).toEqual(EMPTY_KNOWLEDGE_GRAPH);
	});

	it("returns nodes without edges when mock content has a single artifact", async () => {
		const repository = new MockGraphRepository();

		const graph = await repository.getKnowledgeGraph("content-4");

		expect(graph.nodes).toHaveLength(1);
		expect(graph.edges).toEqual([]);
	});
});
