import type {
	LearningProfile,
	LearningRecommendationsResponse,
	UpdateLearningPreferencesRequest,
} from "./types";

export interface LearningRepository {
	getProfile(scopeKey?: string): Promise<LearningProfile>;

	getRecommendations(
		scopeKey?: string,
	): Promise<LearningRecommendationsResponse>;

	updatePreferences(
		request: UpdateLearningPreferencesRequest,
	): Promise<LearningProfile>;

	reset(scopeKey?: string): Promise<LearningProfile>;
}
