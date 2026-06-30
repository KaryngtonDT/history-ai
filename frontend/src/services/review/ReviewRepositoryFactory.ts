import { FEATURES } from "@/config/features";
import { createHttpReviewRepository } from "./HttpReviewRepository";
import { MockReviewRepository } from "./MockReviewRepository";
import type { ReviewRepository } from "./ReviewRepository";

export function createReviewRepository(): ReviewRepository {
	if (FEATURES.USE_MOCK) {
		return new MockReviewRepository();
	}

	return createHttpReviewRepository();
}
