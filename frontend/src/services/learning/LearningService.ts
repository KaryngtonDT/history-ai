import type { LearningRepository } from "./LearningRepository";
import { createLearningRepository } from "./LearningRepositoryFactory";
import type {
	LearningProfile,
	LearningRecommendationsResponse,
	UpdateLearningPreferencesRequest,
} from "./types";

export class LearningService {
	private readonly repository: LearningRepository;

	constructor(repository: LearningRepository = createLearningRepository()) {
		this.repository = repository;
	}

	getProfile(scopeKey?: string): Promise<LearningProfile> {
		return this.repository.getProfile(scopeKey);
	}

	getRecommendations(
		scopeKey?: string,
	): Promise<LearningRecommendationsResponse> {
		return this.repository.getRecommendations(scopeKey);
	}

	updatePreferences(
		request: UpdateLearningPreferencesRequest,
	): Promise<LearningProfile> {
		return this.repository.updatePreferences(request);
	}

	reset(scopeKey?: string): Promise<LearningProfile> {
		return this.repository.reset(scopeKey);
	}
}

export const learningService = new LearningService();
