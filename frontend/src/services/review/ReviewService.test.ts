import { describe, expect, it } from "vitest";
import { MockReviewRepository } from "./MockReviewRepository";
import { ReviewService } from "./ReviewService";
import { DEFAULT_REVIEW_SCORES } from "./types";

describe("ReviewService", () => {
	it("loads and sorts reviews", async () => {
		const service = new ReviewService(new MockReviewRepository());
		const reviews = await service.loadReviews(
			"550e8400-e29b-41d4-a716-446655450101",
		);

		expect(reviews.length).toBeGreaterThan(0);
		expect(service.sortedReviews(reviews)[0].comment).toContain("robotic");
	});

	it("saves a review and returns preference profile", async () => {
		const service = new ReviewService(new MockReviewRepository());
		const review = await service.saveReview(
			"550e8400-e29b-41d4-a716-446655450099",
			{
				executionVersionNumber: 1,
				scores: DEFAULT_REVIEW_SCORES,
				comment: "Looks good.",
			},
		);

		expect(review.comment).toBe("Looks good.");

		const profile = await service.loadPreferenceProfile();
		expect(profile?.reviewCount).toBeGreaterThan(0);
	});
});
