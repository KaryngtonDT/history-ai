export type GoalCategory =
	| "career"
	| "language"
	| "programming"
	| "history"
	| "philosophy"
	| "university"
	| "certification"
	| "personal"
	| "custom";

export type GoalPriority = "primary" | "secondary" | "background";

export type GoalStatus = "active" | "paused" | "completed" | "archived";

export type MentorMissionStatus = "upcoming" | "active" | "completed";

export type RoadmapHorizon = "today" | "week" | "month" | "quarter" | "goal";

export interface GoalConstraint {
	key: string;
	label: string;
	detail: string;
}

export interface LearningGoal {
	id: string;
	title: string;
	description: string;
	motivation: string;
	category: GoalCategory;
	priority: GoalPriority;
	status: GoalStatus;
	progressPercent: number;
	deadline: string | null;
	targetSkills: string[];
	requiredKnowledge: string[];
	successCriteria: string[];
	constraints: GoalConstraint[];
}

export interface GoalMilestone {
	id: string;
	goalId: string;
	label: string;
	detail: string;
	completed: boolean;
	targetAt: string | null;
	completedAt: string | null;
}

export interface MentorMission {
	id: string;
	goalId: string;
	title: string;
	objective: string;
	durationMinutes: number;
	prerequisiteKeys: string[];
	exerciseCount: number;
	validationLabel: string;
	unlockedConceptKey: string;
	status: MentorMissionStatus;
	progressPercent: number;
}

export interface RoadmapStep {
	horizon: RoadmapHorizon;
	label: string;
	detail: string;
	order: number;
}

export interface SkillProgress {
	key: string;
	label: string;
	percent: number;
}

export interface WeeklyReview {
	summary: string;
	progressDelta: number;
	milestonesCompleted: number;
	difficultyNote: string;
	recommendations: string[];
	adaptationPending: boolean;
	generatedAt: string | null;
}

export interface GoalImpact {
	goalId: string;
	goalTitle: string;
	impactPercent: number;
	reason: string;
}

export interface MentorPlan {
	id: string;
	scopeKey: string;
	mentorEnabled: boolean;
	missions: MentorMission[];
	roadmap: RoadmapStep[];
	skills: SkillProgress[];
	milestones: GoalMilestone[];
	currentMissionId: string | null;
	estimatedCompletionAt: string | null;
	weeklyReview: WeeklyReview;
}

export interface MentorDashboard {
	scopeKey: string;
	primaryGoal: LearningGoal | null;
	plan: MentorPlan;
	currentMission: MentorMission | null;
	nextMilestone: GoalMilestone | null;
	goalImpact: GoalImpact[];
}

export interface GoalsResponse {
	scopeKey: string;
	goals: LearningGoal[];
	primaryGoal: LearningGoal | null;
}

export interface MissionsResponse {
	scopeKey: string;
	missions: MentorMission[];
	currentMission: MentorMission | null;
}

export interface RoadmapResponse {
	scopeKey: string;
	roadmap: RoadmapStep[];
}

export interface CreateLearningGoalRequest {
	title: string;
	category?: GoalCategory;
	priority?: GoalPriority;
	description?: string;
	motivation?: string;
	scopeKey?: string;
}

export interface UpdateLearningGoalRequest {
	title?: string;
	category?: GoalCategory;
	priority?: GoalPriority;
	status?: GoalStatus;
	description?: string;
	motivation?: string;
	progressPercent?: number;
	deadline?: string | null;
	targetSkills?: string[];
	requiredKnowledge?: string[];
	successCriteria?: string[];
	scopeKey?: string;
}

export interface CompleteMissionRequest {
	scopeKey?: string;
}
