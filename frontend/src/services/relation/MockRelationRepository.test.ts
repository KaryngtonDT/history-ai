import { describe, expect, it } from "vitest";
import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import { MockRelationRepository } from "./MockRelationRepository";

const transcriptArtifactId = `artifact-transcript-${ROMAN_EMPIRE_CONTENT_ID}`;
const summaryArtifactId = `artifact-summary-${ROMAN_EMPIRE_CONTENT_ID}`;

describe("MockRelationRepository", () => {
	it("returns derived_from relation for mock transcript and summary", async () => {
		const repository = new MockRelationRepository();

		const relations = await repository.getArtifactRelations(
			ROMAN_EMPIRE_CONTENT_ID,
		);

		expect(relations).toContainEqual({
			sourceArtifactId: summaryArtifactId,
			targetArtifactId: transcriptArtifactId,
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
