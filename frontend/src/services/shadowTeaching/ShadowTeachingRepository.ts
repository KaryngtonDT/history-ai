import type {
	AnswerTeachingExerciseRequest,
	CompleteTeachingCheckpointRequest,
	TeachingCheckpointCompleteResult,
	TeachingCurrentResponse,
	TeachingExerciseAnswerResult,
	TeachingPlan,
	UpdateTeachingPreferencesRequest,
} from "./types";

export interface ShadowTeachingRepository {
	getPath(scopeKey?: string): Promise<TeachingPlan>;
	getCurrent(scopeKey?: string): Promise<TeachingCurrentResponse>;
	getObjectives(scopeKey?: string): Promise<{
		scopeKey: string;
		objectives: TeachingPlan["objectives"];
	}>;
	getRevisions(scopeKey?: string): Promise<{
		scopeKey: string;
		revisions: TeachingPlan["revisions"];
	}>;
	getExercises(scopeKey?: string): Promise<{
		scopeKey: string;
		exercises: TeachingPlan["exercises"];
	}>;
	answerExercise(
		exerciseId: string,
		request: AnswerTeachingExerciseRequest,
	): Promise<TeachingExerciseAnswerResult>;
	completeCheckpoint(
		checkpointId: string,
		request?: CompleteTeachingCheckpointRequest,
	): Promise<TeachingCheckpointCompleteResult>;
	updatePreferences(
		request: UpdateTeachingPreferencesRequest,
	): Promise<TeachingPlan["preferences"]>;
	reset(scopeKey?: string): Promise<TeachingPlan>;
}
