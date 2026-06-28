import { describe, expect, it } from "vitest";
import { mapRecommendedArtifactFromApi } from "./types";

describe("recommendation types", () => {
	it("maps score from API response", () => {
		const result = mapRecommendedArtifactFromApi({
			artifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "transcript",
			title: "Transcript",
			reason: "derived_from",
			score: 100,
		});

		expect(result.score).toBe(100);
	});

	it("omits invalid score values from API response", () => {
		const result = mapRecommendedArtifactFromApi({
			artifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "transcript",
			title: "Transcript",
			reason: "derived_from",
			score: 150,
		});

		expect(result.score).toBeUndefined();
	});

	it("works when score is missing from API response", () => {
		const result = mapRecommendedArtifactFromApi({
			artifactId: "550e8400-e29b-41d4-a716-446655440001",
			type: "transcript",
			title: "Transcript",
			reason: "derived_from",
		});

		expect(result.score).toBeUndefined();
	});
});
