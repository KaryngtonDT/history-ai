import { describe, expect, it } from "vitest";
import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import { MockRecommendationRepository } from "./MockRecommendationRepository";

const transcriptArtifactId = `artifact-transcript-${ROMAN_EMPIRE_CONTENT_ID}`;
const summaryArtifactId = `artifact-summary-${ROMAN_EMPIRE_CONTENT_ID}`;

describe("MockRecommendationRepository", () => {
	it("returns transcript recommendation for mock summary artifact", async () => {
		const repository = new MockRecommendationRepository();

		const recommendations = await repository.getArtifactRecommendations(
			ROMAN_EMPIRE_CONTENT_ID,
			summaryArtifactId,
		);

		expect(recommendations).toEqual([
			{
				artifactId: transcriptArtifactId,
				type: "transcript",
				title: "Transcript",
				reason: "derived_from",
				score: 100,
			},
		]);
	});

	it("returns empty array when mock content has no artifacts", async () => {
		const repository = new MockRecommendationRepository();

		const recommendations = await repository.getArtifactRecommendations(
			"missing-content",
			"artifact-summary-1",
		);

		expect(recommendations).toEqual([]);
	});

	it("returns empty array when current artifact is unknown in mock content", async () => {
		const repository = new MockRecommendationRepository();

		const recommendations = await repository.getArtifactRecommendations(
			ROMAN_EMPIRE_CONTENT_ID,
			"artifact-unknown-1",
		);

		expect(recommendations).toEqual([]);
	});
});
