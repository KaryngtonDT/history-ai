import type {
	CompleteMissionRequest,
	CreateLearningGoalRequest,
	GoalsResponse,
	LearningGoal,
	MentorDashboard,
	MentorMission,
	MissionsResponse,
	RoadmapResponse,
	UpdateLearningGoalRequest,
} from "./types";

export interface ShadowMentorRepository {
	getDashboard(scopeKey?: string): Promise<MentorDashboard>;
	getGoals(scopeKey?: string): Promise<GoalsResponse>;
	createGoal(request: CreateLearningGoalRequest): Promise<LearningGoal>;
	updateGoal(
		id: string,
		request: UpdateLearningGoalRequest,
	): Promise<LearningGoal>;
	deleteGoal(id: string, scopeKey?: string): Promise<void>;
	getMissions(scopeKey?: string): Promise<MissionsResponse>;
	getRoadmap(scopeKey?: string): Promise<RoadmapResponse>;
	completeMission(
		missionId: string,
		request?: CompleteMissionRequest,
	): Promise<MentorMission>;
	resetGoals(scopeKey?: string): Promise<GoalsResponse>;
}
