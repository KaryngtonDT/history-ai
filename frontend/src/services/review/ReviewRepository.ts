import type { PreferenceProfile, Review, SaveReviewInput } from "./types";

export interface ReviewRepository {
	getReviews(videoId: string): Promise<Review[]>;
	saveReview(videoId: string, input: SaveReviewInput): Promise<Review>;
	getPreferenceProfile(): Promise<PreferenceProfile | null>;
}
