import type { ShadowTeachingRepository } from "./ShadowTeachingRepository";
import { createShadowTeachingRepository } from "./ShadowTeachingRepositoryFactory";
import type {
	AnswerTeachingExerciseRequest,
	CompleteTeachingCheckpointRequest,
	UpdateTeachingPreferencesRequest,
} from "./types";

export class ShadowTeachingService {
	private readonly repository: ShadowTeachingRepository;

	constructor(
		repository: ShadowTeachingRepository = createShadowTeachingRepository(),
	) {
		this.repository = repository;
	}

	getPath(scopeKey?: string) {
		return this.repository.getPath(scopeKey);
	}

	getCurrent(scopeKey?: string) {
		return this.repository.getCurrent(scopeKey);
	}

	getObjectives(scopeKey?: string) {
		return this.repository.getObjectives(scopeKey);
	}

	getRevisions(scopeKey?: string) {
		return this.repository.getRevisions(scopeKey);
	}

	getExercises(scopeKey?: string) {
		return this.repository.getExercises(scopeKey);
	}

	answerExercise(exerciseId: string, request: AnswerTeachingExerciseRequest) {
		return this.repository.answerExercise(exerciseId, request);
	}

	completeCheckpoint(
		checkpointId: string,
		request?: CompleteTeachingCheckpointRequest,
	) {
		return this.repository.completeCheckpoint(checkpointId, request);
	}

	updatePreferences(request: UpdateTeachingPreferencesRequest) {
		return this.repository.updatePreferences(request);
	}

	reset(scopeKey?: string) {
		return this.repository.reset(scopeKey);
	}
}

export const shadowTeachingService = new ShadowTeachingService();
