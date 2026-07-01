import {
	LEARNING_PREFERENCES_PATH,
	LEARNING_PROFILE_PATH,
	LEARNING_RECOMMENDATIONS_PATH,
	LEARNING_RESET_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { LearningRepository } from "./LearningRepository";
import type {
	LearningProfile,
	LearningRecommendationsResponse,
	UpdateLearningPreferencesRequest,
} from "./types";

export class HttpLearningRepository implements LearningRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getProfile(scopeKey = "default"): Promise<LearningProfile> {
		const params = new URLSearchParams({ scopeKey });
		return this.httpClient.get<LearningProfile>(
			`${LEARNING_PROFILE_PATH}?${params.toString()}`,
		);
	}

	async getRecommendations(
		scopeKey = "default",
	): Promise<LearningRecommendationsResponse> {
		const params = new URLSearchParams({ scopeKey });
		return this.httpClient.get<LearningRecommendationsResponse>(
			`${LEARNING_RECOMMENDATIONS_PATH}?${params.toString()}`,
		);
	}

	async updatePreferences(
		request: UpdateLearningPreferencesRequest,
	): Promise<LearningProfile> {
		return this.httpClient.put<LearningProfile>(
			LEARNING_PREFERENCES_PATH,
			request,
		);
	}

	async reset(scopeKey = "default"): Promise<LearningProfile> {
		return this.httpClient.post<LearningProfile>(LEARNING_RESET_PATH, {
			scopeKey,
		});
	}
}
