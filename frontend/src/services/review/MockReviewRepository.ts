import type { ReviewRepository } from "./ReviewRepository";
import type {
	PreferenceProfile,
	Review,
	ReviewScores,
	SaveReviewInput,
} from "./types";
import { DEFAULT_REVIEW_SCORES } from "./types";

export const MOCK_PREVIEW_REVIEWS: Review[] = [
	{
		id: "550e8400-e29b-41d4-a716-446655470001",
		videoId: "550e8400-e29b-41d4-a716-446655450101",
		executionVersionNumber: 2,
		scores: {
			overall: 4,
			translation: 5,
			voice_clone: 3,
			lip_sync: 5,
			rendering: 5,
		},
		comment: "The cloned voice is slightly too robotic.",
		createdAt: "2026-06-26T10:00:00+00:00",
	},
];

export const MOCK_PREVIEW_PREFERENCE_PROFILE: PreferenceProfile = {
	translationStyle: "natural",
	voiceStability: "high",
	renderingPreset: "quality",
	lipSyncStrength: "subtle",
	latestComment: "The cloned voice is slightly too robotic.",
	reviewCount: 1,
	explanationLines: [
		"Using your preferred voice profile with increased stability.",
		"Lip sync strength reduced according to previous feedback.",
	],
};

export class MockReviewRepository implements ReviewRepository {
	private readonly reviews = new Map<string, Review[]>();

	constructor() {
		this.reviews.set(
			"550e8400-e29b-41d4-a716-446655450101",
			MOCK_PREVIEW_REVIEWS.map((review) => ({ ...review })),
		);
	}

	async getReviews(videoId: string): Promise<Review[]> {
		return (this.reviews.get(videoId) ?? []).map((review) => ({
			...review,
			scores: { ...review.scores },
		}));
	}

	async saveReview(videoId: string, input: SaveReviewInput): Promise<Review> {
		const review: Review = {
			id: crypto.randomUUID(),
			videoId,
			executionVersionNumber: input.executionVersionNumber,
			scores: { ...input.scores },
			comment: input.comment,
			createdAt: new Date().toISOString(),
		};

		const existing = this.reviews.get(videoId) ?? [];
		this.reviews.set(videoId, [...existing, review]);

		return review;
	}

	async getPreferenceProfile(): Promise<PreferenceProfile | null> {
		return { ...MOCK_PREVIEW_PREFERENCE_PROFILE };
	}

	defaultScores(): ReviewScores {
		return { ...DEFAULT_REVIEW_SCORES };
	}
}
