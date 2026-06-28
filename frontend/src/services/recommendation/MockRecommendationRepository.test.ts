import { describe, expect, it } from "vitest";
import { MockRecommendationRepository } from "./MockRecommendationRepository";

describe("MockRecommendationRepository", () => {
	it("returns transcript recommendation for mock summary artifact", async () => {
		const repository = new MockRecommendationRepository();

		const recommendations = await repository.getArtifactRecommendations(
			"1",
			"artifact-summary-1",
		);

		expect(recommendations).toEqual([
			{
				artifactId: "artifact-transcript-1",
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
			"1",
			"artifact-unknown-1",
		);

		expect(recommendations).toEqual([]);
	});
});
