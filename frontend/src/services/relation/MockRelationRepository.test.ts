import { describe, expect, it } from "vitest";
import { MockRelationRepository } from "./MockRelationRepository";

describe("MockRelationRepository", () => {
	it("returns derived_from relation for mock transcript and summary", async () => {
		const repository = new MockRelationRepository();

		const relations = await repository.getArtifactRelations("1");

		expect(relations).toContainEqual({
			sourceArtifactId: "artifact-summary-1",
			targetArtifactId: "artifact-transcript-1",
			type: "derived_from",
		});
	});

	it("returns empty array when mock content has no artifacts", async () => {
		const repository = new MockRelationRepository();

		const relations = await repository.getArtifactRelations("missing-content");

		expect(relations).toEqual([]);
	});

	it("returns empty array when mock content has a single artifact", async () => {
		const repository = new MockRelationRepository();

		const relations = await repository.getArtifactRelations("content-4");

		expect(relations).toEqual([]);
	});
});
