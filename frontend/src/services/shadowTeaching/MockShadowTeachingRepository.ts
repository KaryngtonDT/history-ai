import type { ShadowTeachingRepository } from "./ShadowTeachingRepository";
import type {
	AnswerTeachingExerciseRequest,
	CompleteTeachingCheckpointRequest,
	TeachingCheckpoint,
	TeachingCheckpointCompleteResult,
	TeachingCurrentResponse,
	TeachingExerciseAnswerResult,
	TeachingPlan,
	UpdateTeachingPreferencesRequest,
} from "./types";

const defaultPlan: TeachingPlan = {
	id: "33333333-3333-4333-8333-333333333333",
	scopeKey: "default",
	learningPath: [
		{
			id: "mission-foundations",
			label: "Foundations",
			detail: "Build strong comprehension basics.",
			status: "active",
			progressPercent: 58,
			checkpointIds: ["checkpoint-1", "checkpoint-2"],
		},
		{
			id: "mission-fluency",
			label: "Fluency Sprint",
			detail: "Increase response speed and confidence.",
			status: "upcoming",
			progressPercent: 10,
			checkpointIds: ["checkpoint-3"],
		},
	],
	currentLesson: {
		id: "lesson-12",
		title: "Comparing past and present tense",
		summary: "Practice tense switching with short dialogues.",
		missionId: "mission-foundations",
		nextCheckpointId: "checkpoint-2",
		exercisesToday: 3,
		revisionDue: 2,
	},
	objectives: [
		{
			id: "objective-1",
			label: "Recognize tense changes",
			detail: "Identify verb tense in context quickly.",
			status: "active",
			progressPercent: 70,
		},
		{
			id: "objective-2",
			label: "Answer follow-up questions",
			detail: "Respond with complete sentence patterns.",
			status: "pending",
			progressPercent: 25,
		},
	],
	exercises: [
		{
			id: "exercise-1",
			title: "Quick tense selection",
			prompt: "Choose the correct tense for each sentence.",
			difficulty: "medium",
			status: "new",
			nextReviewAt: "2026-07-03T08:00:00+00:00",
		},
		{
			id: "exercise-2",
			title: "Mini dialogue response",
			prompt: "Reply in two lines using past tense.",
			difficulty: "medium",
			status: "needs_revision",
			nextReviewAt: "2026-07-02T16:00:00+00:00",
		},
	],
	revisions: [
		{
			id: "revision-1",
			label: "Irregular verbs recap",
			reason: "Two recent mistakes in session responses.",
			dueAt: "2026-07-02T18:00:00+00:00",
			priority: "high",
		},
		{
			id: "revision-2",
			label: "Question inversion review",
			reason: "Keep inversion pattern stable.",
			dueAt: "2026-07-03T09:00:00+00:00",
			priority: "normal",
		},
	],
	checkpoints: [
		{
			id: "checkpoint-1",
			label: "Lesson sequencing",
			detail: "Complete three lesson loops in order.",
			status: "completed",
			targetAt: "2026-06-28T10:00:00+00:00",
			completedAt: "2026-06-27T13:00:00+00:00",
		},
		{
			id: "checkpoint-2",
			label: "Tense confidence check",
			detail: "Score at least 80% in exercise block.",
			status: "ready",
			targetAt: "2026-07-03T12:00:00+00:00",
		},
		{
			id: "checkpoint-3",
			label: "Fluency baseline",
			detail: "Hold a 2-minute guided conversation.",
			status: "upcoming",
			targetAt: "2026-07-07T12:00:00+00:00",
		},
	],
	progress: {
		completedObjectives: 1,
		totalObjectives: 3,
		completedExercises: 5,
		totalExercises: 12,
		completedCheckpoints: 1,
		totalCheckpoints: 3,
	},
	history: [
		{
			id: "history-1",
			label: "Checkpoint completed",
			detail: "Lesson sequencing passed.",
			recordedAt: "2026-06-27T13:00:00+00:00",
		},
		{
			id: "history-2",
			label: "Exercise corrected",
			detail: "Mini dialogue improved after revision.",
			recordedAt: "2026-07-01T09:00:00+00:00",
		},
	],
	preferences: {
		voiceMode: "coach",
		autoCheckpoint: true,
		remindersEnabled: true,
	},
};

export class MockShadowTeachingRepository implements ShadowTeachingRepository {
	private plan: TeachingPlan = structuredClone(defaultPlan);

	getPath(): Promise<TeachingPlan> {
		return Promise.resolve(structuredClone(this.plan));
	}

	getCurrent(): Promise<TeachingCurrentResponse> {
		const nextCheckpoint = this.plan.checkpoints.find(
			(checkpoint) => checkpoint.status !== "completed",
		);

		return Promise.resolve({
			scopeKey: this.plan.scopeKey,
			lesson: structuredClone(this.plan.currentLesson),
			nextCheckpoint: nextCheckpoint ? structuredClone(nextCheckpoint) : null,
			exercisesDue: this.plan.exercises.filter(
				(exercise) =>
					exercise.status === "new" || exercise.status === "needs_revision",
			).length,
			revisionDue: this.plan.revisions.length,
		});
	}

	getObjectives() {
		return Promise.resolve({
			scopeKey: this.plan.scopeKey,
			objectives: structuredClone(this.plan.objectives),
		});
	}

	getRevisions() {
		return Promise.resolve({
			scopeKey: this.plan.scopeKey,
			revisions: structuredClone(this.plan.revisions),
		});
	}

	getExercises() {
		return Promise.resolve({
			scopeKey: this.plan.scopeKey,
			exercises: structuredClone(this.plan.exercises),
		});
	}

	answerExercise(
		exerciseId: string,
		request: AnswerTeachingExerciseRequest,
	): Promise<TeachingExerciseAnswerResult> {
		const exercise = this.plan.exercises.find((item) => item.id === exerciseId);

		if (!exercise) {
			return Promise.resolve({
				correct: false,
				feedback: "Exercise not found in teaching plan.",
				exercise: {
					id: exerciseId,
					title: "Unknown exercise",
					prompt: "",
					difficulty: "unknown",
					status: "needs_revision",
				},
				progress: structuredClone(this.plan.progress),
			});
		}

		const normalized = request.answer.trim().toLowerCase();
		const correct = normalized.length > 4;
		exercise.status = correct ? "correct" : "needs_revision";

		if (correct) {
			this.plan.progress.completedExercises += 1;
		}

		this.plan.history.unshift({
			id: `history-${Date.now()}`,
			label: correct ? "Exercise completed" : "Exercise needs revision",
			detail: exercise.title,
			recordedAt: new Date().toISOString(),
		});

		return Promise.resolve({
			correct,
			feedback: correct
				? "Great answer. This exercise is marked complete."
				: "Keep practicing and try again with a longer answer.",
			exercise: structuredClone(exercise),
			progress: structuredClone(this.plan.progress),
		});
	}

	completeCheckpoint(
		checkpointId: string,
		request: CompleteTeachingCheckpointRequest = {},
	): Promise<TeachingCheckpointCompleteResult> {
		void request;
		const checkpoint = this.plan.checkpoints.find(
			(item) => item.id === checkpointId,
		);

		if (checkpoint && checkpoint.status !== "completed") {
			checkpoint.status = "completed";
			checkpoint.completedAt = new Date().toISOString();
			this.plan.progress.completedCheckpoints += 1;
		}

		return Promise.resolve({
			checkpoint: structuredClone(
				checkpoint ??
					({
						id: checkpointId,
						label: "Unknown checkpoint",
						detail: "",
						status: "upcoming",
					} satisfies TeachingCheckpoint),
			),
			progress: structuredClone(this.plan.progress),
		});
	}

	updatePreferences(request: UpdateTeachingPreferencesRequest) {
		if (request.voiceMode) {
			this.plan.preferences.voiceMode = request.voiceMode;
		}

		if (typeof request.autoCheckpoint === "boolean") {
			this.plan.preferences.autoCheckpoint = request.autoCheckpoint;
		}

		if (typeof request.remindersEnabled === "boolean") {
			this.plan.preferences.remindersEnabled = request.remindersEnabled;
		}

		return Promise.resolve(structuredClone(this.plan.preferences));
	}

	reset(): Promise<TeachingPlan> {
		this.plan = structuredClone({
			...defaultPlan,
			progress: {
				...defaultPlan.progress,
				completedExercises: 0,
				completedCheckpoints: 0,
			},
			history: [],
		});

		return this.getPath();
	}
}
