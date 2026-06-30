import type { ReviewRepository } from "./ReviewRepository";
import { createReviewRepository } from "./ReviewRepositoryFactory";
import type {
	PreferenceProfile,
	Review,
	ReviewCategory,
	ReviewScores,
	SaveReviewInput,
} from "./types";
import { DEFAULT_REVIEW_SCORES, REVIEW_CATEGORY_LABELS } from "./types";

export class ReviewService {
	private readonly repository: ReviewRepository;

	constructor(repository: ReviewRepository) {
		this.repository = repository;
	}

	loadReviews(videoId: string): Promise<Review[]> {
		return this.repository.getReviews(videoId);
	}

	saveReview(videoId: string, input: SaveReviewInput): Promise<Review> {
		return this.repository.saveReview(videoId, input);
	}

	loadPreferenceProfile(): Promise<PreferenceProfile | null> {
		return this.repository.getPreferenceProfile();
	}

	defaultScores(): ReviewScores {
		return { ...DEFAULT_REVIEW_SCORES };
	}

	formatCategory(category: ReviewCategory): string {
		return REVIEW_CATEGORY_LABELS[category];
	}

	sortedReviews(reviews: Review[]): Review[] {
		return [...reviews].sort(
			(left, right) =>
				new Date(right.createdAt).getTime() -
				new Date(left.createdAt).getTime(),
		);
	}
}

export const reviewService = new ReviewService(createReviewRepository());
