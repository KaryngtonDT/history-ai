import {
	SHADOW_GOALS_PATH,
	SHADOW_GOALS_RESET_PATH,
	SHADOW_MENTOR_PATH,
	SHADOW_MISSION_COMPLETE_PATH,
	SHADOW_MISSIONS_PATH,
	SHADOW_ROADMAP_PATH,
	shadowGoalPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowMentorRepository } from "./ShadowMentorRepository";
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

export class HttpShadowMentorRepository implements ShadowMentorRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getDashboard(scopeKey?: string): Promise<MentorDashboard> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<MentorDashboard>(
			`${SHADOW_MENTOR_PATH}${query}`,
		);
	}

	getGoals(scopeKey?: string): Promise<GoalsResponse> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<GoalsResponse>(`${SHADOW_GOALS_PATH}${query}`);
	}

	createGoal(request: CreateLearningGoalRequest): Promise<LearningGoal> {
		return this.httpClient.post<LearningGoal>(SHADOW_GOALS_PATH, request);
	}

	updateGoal(
		id: string,
		request: UpdateLearningGoalRequest,
	): Promise<LearningGoal> {
		return this.httpClient.put<LearningGoal>(shadowGoalPath(id), request);
	}

	deleteGoal(id: string, scopeKey?: string): Promise<void> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.delete(`${shadowGoalPath(id)}${query}`);
	}

	getMissions(scopeKey?: string): Promise<MissionsResponse> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<MissionsResponse>(
			`${SHADOW_MISSIONS_PATH}${query}`,
		);
	}

	getRoadmap(scopeKey?: string): Promise<RoadmapResponse> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<RoadmapResponse>(
			`${SHADOW_ROADMAP_PATH}${query}`,
		);
	}

	completeMission(
		missionId: string,
		request: CompleteMissionRequest = {},
	): Promise<MentorMission> {
		return this.httpClient.post<MentorMission>(
			SHADOW_MISSION_COMPLETE_PATH(missionId),
			request,
		);
	}

	resetGoals(scopeKey?: string): Promise<GoalsResponse> {
		return this.httpClient.post<GoalsResponse>(
			SHADOW_GOALS_RESET_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}
}
