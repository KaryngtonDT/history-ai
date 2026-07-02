export type TeachingObjectiveStatus = "pending" | "active" | "completed";

export type TeachingExerciseStatus =
	| "new"
	| "in_progress"
	| "correct"
	| "needs_revision";

export type TeachingCheckpointStatus = "upcoming" | "ready" | "completed";

export type TeachingMissionStatus = "upcoming" | "active" | "completed";

export type TeachingVoiceMode = "coach" | "mentor" | "story";

export interface TeachingObjective {
	id: string;
	label: string;
	detail: string;
	status: TeachingObjectiveStatus;
	progressPercent: number;
}

export interface TeachingExercise {
	id: string;
	title: string;
	prompt: string;
	difficulty: string;
	status: TeachingExerciseStatus;
	nextReviewAt?: string | null;
}

export interface TeachingRevision {
	id: string;
	label: string;
	reason: string;
	dueAt: string;
	priority: string;
}

export interface TeachingCheckpoint {
	id: string;
	label: string;
	detail: string;
	status: TeachingCheckpointStatus;
	targetAt?: string | null;
	completedAt?: string | null;
}

export interface TeachingMission {
	id: string;
	label: string;
	detail: string;
	status: TeachingMissionStatus;
	progressPercent: number;
	checkpointIds: string[];
}

export interface TeachingCurrentLesson {
	id: string;
	title: string;
	summary: string;
	missionId?: string | null;
	nextCheckpointId?: string | null;
	exercisesToday: number;
	revisionDue: number;
}

export interface TeachingProgressSnapshot {
	completedObjectives: number;
	totalObjectives: number;
	completedExercises: number;
	totalExercises: number;
	completedCheckpoints: number;
	totalCheckpoints: number;
}

export interface TeachingHistoryEntry {
	id: string;
	label: string;
	detail: string;
	recordedAt: string;
}

export interface TeachingPreferences {
	voiceMode: TeachingVoiceMode;
	autoCheckpoint: boolean;
	remindersEnabled: boolean;
}

export interface TeachingPlan {
	id: string;
	scopeKey: string;
	learningPath: TeachingMission[];
	currentLesson: TeachingCurrentLesson | null;
	objectives: TeachingObjective[];
	exercises: TeachingExercise[];
	revisions: TeachingRevision[];
	checkpoints: TeachingCheckpoint[];
	progress: TeachingProgressSnapshot;
	history: TeachingHistoryEntry[];
	preferences: TeachingPreferences;
}

export interface AnswerTeachingExerciseRequest {
	answer: string;
	scopeKey?: string;
}

export interface CompleteTeachingCheckpointRequest {
	scopeKey?: string;
}

export interface UpdateTeachingPreferencesRequest {
	scopeKey?: string;
	voiceMode?: TeachingVoiceMode;
	autoCheckpoint?: boolean;
	remindersEnabled?: boolean;
}

export interface TeachingCurrentResponse {
	scopeKey: string;
	lesson: TeachingCurrentLesson | null;
	nextCheckpoint: TeachingCheckpoint | null;
	exercisesDue: number;
	revisionDue: number;
}

export interface TeachingExerciseAnswerResult {
	correct: boolean;
	feedback: string;
	exercise: TeachingExercise;
	progress: TeachingProgressSnapshot;
}

export interface TeachingCheckpointCompleteResult {
	checkpoint: TeachingCheckpoint;
	progress: TeachingProgressSnapshot;
}
