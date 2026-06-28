import { describe, expect, it } from "vitest";
import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import { MockGraphRepository } from "./MockGraphRepository";
import { EMPTY_KNOWLEDGE_GRAPH } from "./types";

const transcriptArtifactId = `artifact-transcript-${ROMAN_EMPIRE_CONTENT_ID}`;
const summaryArtifactId = `artifact-summary-${ROMAN_EMPIRE_CONTENT_ID}`;

describe("MockGraphRepository", () => {
	it("returns graph nodes and derived_from edge for mock transcript and summary", async () => {
		const repository = new MockGraphRepository();

		const graph = await repository.getKnowledgeGraph(ROMAN_EMPIRE_CONTENT_ID);

		expect(graph.nodes).toEqual([
			{
				artifactId: transcriptArtifactId,
				type: "transcript",
				title: "Transcript",
			},
			{
				artifactId: summaryArtifactId,
				type: "summary",
				title: "Summary",
			},
		]);
		expect(graph.edges).toContainEqual({
			sourceArtifactId: summaryArtifactId,
			targetArtifactId: transcriptArtifactId,
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
