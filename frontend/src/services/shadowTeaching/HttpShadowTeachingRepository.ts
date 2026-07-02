import {
	SHADOW_TEACHING_CHECKPOINT_COMPLETE_PATH,
	SHADOW_TEACHING_CURRENT_PATH,
	SHADOW_TEACHING_EXERCISE_ANSWER_PATH,
	SHADOW_TEACHING_EXERCISES_PATH,
	SHADOW_TEACHING_OBJECTIVES_PATH,
	SHADOW_TEACHING_PATH_PATH,
	SHADOW_TEACHING_PREFERENCES_PATH,
	SHADOW_TEACHING_RESET_PATH,
	SHADOW_TEACHING_REVISIONS_PATH,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ShadowTeachingRepository } from "./ShadowTeachingRepository";
import type {
	AnswerTeachingExerciseRequest,
	CompleteTeachingCheckpointRequest,
	TeachingCheckpointCompleteResult,
	TeachingCurrentResponse,
	TeachingExerciseAnswerResult,
	TeachingPlan,
	UpdateTeachingPreferencesRequest,
} from "./types";

export class HttpShadowTeachingRepository implements ShadowTeachingRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	getPath(scopeKey?: string): Promise<TeachingPlan> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<TeachingPlan>(
			`${SHADOW_TEACHING_PATH_PATH}${query}`,
		);
	}

	getCurrent(scopeKey?: string): Promise<TeachingCurrentResponse> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<TeachingCurrentResponse>(
			`${SHADOW_TEACHING_CURRENT_PATH}${query}`,
		);
	}

	getObjectives(scopeKey?: string): Promise<{
		scopeKey: string;
		objectives: TeachingPlan["objectives"];
	}> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<{
			scopeKey: string;
			objectives: TeachingPlan["objectives"];
		}>(`${SHADOW_TEACHING_OBJECTIVES_PATH}${query}`);
	}

	getRevisions(scopeKey?: string): Promise<{
		scopeKey: string;
		revisions: TeachingPlan["revisions"];
	}> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<{
			scopeKey: string;
			revisions: TeachingPlan["revisions"];
		}>(`${SHADOW_TEACHING_REVISIONS_PATH}${query}`);
	}

	getExercises(scopeKey?: string): Promise<{
		scopeKey: string;
		exercises: TeachingPlan["exercises"];
	}> {
		const query = scopeKey ? `?scopeKey=${encodeURIComponent(scopeKey)}` : "";

		return this.httpClient.get<{
			scopeKey: string;
			exercises: TeachingPlan["exercises"];
		}>(`${SHADOW_TEACHING_EXERCISES_PATH}${query}`);
	}

	answerExercise(
		exerciseId: string,
		request: AnswerTeachingExerciseRequest,
	): Promise<TeachingExerciseAnswerResult> {
		return this.httpClient.post<TeachingExerciseAnswerResult>(
			SHADOW_TEACHING_EXERCISE_ANSWER_PATH(exerciseId),
			request,
		);
	}

	completeCheckpoint(
		checkpointId: string,
		request: CompleteTeachingCheckpointRequest = {},
	): Promise<TeachingCheckpointCompleteResult> {
		return this.httpClient.post<TeachingCheckpointCompleteResult>(
			SHADOW_TEACHING_CHECKPOINT_COMPLETE_PATH(checkpointId),
			request,
		);
	}

	updatePreferences(
		request: UpdateTeachingPreferencesRequest,
	): Promise<TeachingPlan["preferences"]> {
		return this.httpClient.put<TeachingPlan["preferences"]>(
			SHADOW_TEACHING_PREFERENCES_PATH,
			request,
		);
	}

	reset(scopeKey?: string): Promise<TeachingPlan> {
		return this.httpClient.post<TeachingPlan>(
			SHADOW_TEACHING_RESET_PATH,
			scopeKey ? { scopeKey } : {},
		);
	}
}
