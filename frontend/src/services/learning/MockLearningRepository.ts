import type { LearningRepository } from "./LearningRepository";
import type {
	LearningProfile,
	LearningRecommendationsResponse,
	UpdateLearningPreferencesRequest,
} from "./types";

function createMockProfile(adaptiveEnabled = false): LearningProfile {
	return {
		id: "550e8400-e29b-41d4-a716-446655440010",
		scopeKey: "default",
		adaptiveRecommendationsEnabled: adaptiveEnabled,
		preferences: [
			{
				key: "adaptive_recommendations_enabled",
				enabled: adaptiveEnabled,
			},
		],
		signals: adaptiveEnabled
			? [
					{
						id: "550e8400-e29b-41d4-a716-446655440011",
						type: "repeated_vocabulary",
						recordedAt: "2026-06-26T10:00:00+00:00",
						context: {
							summary: "Repeated vocabulary: epistemology.",
							term: "epistemology",
						},
					},
				]
			: [],
		insights: adaptiveEnabled
			? [
					{
						id: "550e8400-e29b-41d4-a716-446655440012",
						type: "vocabulary_gap",
						summary:
							"Observed 3 vocabulary-related signals, indicating recurring word-meaning questions.",
						sourceSignalIds: ["550e8400-e29b-41d4-a716-446655440011"],
						generatedAt: "2026-06-26T10:01:00+00:00",
					},
				]
			: [],
		recommendations: adaptiveEnabled
			? [
					{
						id: "550e8400-e29b-41d4-a716-446655440013",
						type: "show_vocabulary_before_playback",
						explanation:
							"Repeated vocabulary questions suggest previewing key terms before playback.",
						sourceInsightIds: ["550e8400-e29b-41d4-a716-446655440012"],
						generatedAt: "2026-06-26T10:01:00+00:00",
					},
				]
			: [],
	};
}

export class MockLearningRepository implements LearningRepository {
	private profile = createMockProfile(false);

	async getProfile(): Promise<LearningProfile> {
		return this.profile;
	}

	async getRecommendations(): Promise<LearningRecommendationsResponse> {
		return {
			scopeKey: this.profile.scopeKey,
			adaptiveRecommendationsEnabled:
				this.profile.adaptiveRecommendationsEnabled,
			recommendations: this.profile.recommendations,
			adaptiveHints: {
				active: this.profile.adaptiveRecommendationsEnabled,
				explanationStyle: this.profile.adaptiveRecommendationsEnabled
					? "short"
					: null,
				challengeLevel: this.profile.adaptiveRecommendationsEnabled
					? "easy"
					: null,
				voiceLanguage: this.profile.adaptiveRecommendationsEnabled
					? "fr"
					: null,
				translationStyle: null,
				preferredProvider: null,
				appliedRecommendations: this.profile.recommendations.map(
					(item) => item.type,
				),
			},
			profile: this.profile,
		};
	}

	async updatePreferences(
		request: UpdateLearningPreferencesRequest,
	): Promise<LearningProfile> {
		const enabled = request.adaptiveRecommendationsEnabled ?? false;
		this.profile = createMockProfile(enabled);
		return this.profile;
	}

	async reset(): Promise<LearningProfile> {
		this.profile = createMockProfile(
			this.profile.adaptiveRecommendationsEnabled,
		);
		return this.profile;
	}
}
