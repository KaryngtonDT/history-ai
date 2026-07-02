import type { ShadowMentorRepository } from "./ShadowMentorRepository";
import { createShadowMentorRepository } from "./ShadowMentorRepositoryFactory";
import type {
	CompleteMissionRequest,
	CreateLearningGoalRequest,
	UpdateLearningGoalRequest,
} from "./types";

export class ShadowMentorService {
	private readonly repository: ShadowMentorRepository;

	constructor(
		repository: ShadowMentorRepository = createShadowMentorRepository(),
	) {
		this.repository = repository;
	}

	getDashboard(scopeKey?: string) {
		return this.repository.getDashboard(scopeKey);
	}

	getGoals(scopeKey?: string) {
		return this.repository.getGoals(scopeKey);
	}

	createGoal(request: CreateLearningGoalRequest) {
		return this.repository.createGoal(request);
	}

	updateGoal(id: string, request: UpdateLearningGoalRequest) {
		return this.repository.updateGoal(id, request);
	}

	deleteGoal(id: string, scopeKey?: string) {
		return this.repository.deleteGoal(id, scopeKey);
	}

	getMissions(scopeKey?: string) {
		return this.repository.getMissions(scopeKey);
	}

	getRoadmap(scopeKey?: string) {
		return this.repository.getRoadmap(scopeKey);
	}

	completeMission(missionId: string, request?: CompleteMissionRequest) {
		return this.repository.completeMission(missionId, request);
	}

	resetGoals(scopeKey?: string) {
		return this.repository.resetGoals(scopeKey);
	}
}

export const shadowMentorService = new ShadowMentorService();
