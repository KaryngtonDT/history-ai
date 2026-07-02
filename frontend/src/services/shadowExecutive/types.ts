export type DecisionType =
	| "review"
	| "learn"
	| "skip"
	| "pause"
	| "accelerate"
	| "slow_down"
	| "recommend_video"
	| "recommend_pdf"
	| "recommend_audio"
	| "recommend_exercise"
	| "recommend_mission"
	| "recommend_revision";

export type DecisionStatus =
	| "pending"
	| "approved"
	| "rejected"
	| "deferred"
	| "ignored";

export type ExecutivePriority = "critical" | "high" | "normal" | "low";

export type ExecutiveTaskType =
	| "review"
	| "mission"
	| "watch"
	| "exercise"
	| "checkpoint"
	| "pause";

export type DecisionImpactDimension =
	| "knowledge"
	| "goal"
	| "time"
	| "difficulty"
	| "confidence";

export type EvidenceSource =
	| "knowledge"
	| "memory"
	| "teaching"
	| "mentor"
	| "session";

export interface ExecutiveReason {
	summary: string;
	detail: string;
}

export interface ExecutiveConstraint {
	key: string;
	label: string;
	detail: string;
}

export interface ExecutiveTask {
	id: string;
	type: ExecutiveTaskType;
	label: string;
	detail: string;
	order: number;
	scheduledAt: string | null;
}

export interface ExecutiveAgenda {
	today: ExecutiveTask[];
	upcoming: ExecutiveTask[];
}

export interface ExecutiveDecision {
	id: string;
	type: DecisionType;
	status: DecisionStatus;
	priority: ExecutivePriority;
	title: string;
	summary: string;
	reason: ExecutiveReason;
	evidence: string[];
	impacts: DecisionImpactDimension[];
	linkedGoalId: string | null;
	linkedConceptKey: string | null;
	linkedResourceId: string | null;
	constraint: ExecutiveConstraint | null;
}

export interface ExecutiveRecommendation {
	id: string;
	type: DecisionType;
	title: string;
	detail: string;
	priority: ExecutivePriority;
	conceptKey: string | null;
	resourceId: string | null;
}

export interface WeeklyExecutiveReview {
	summary: string;
	progressPercent: number;
	knowledgeGrowth: number;
	completedMissions: number;
	missedReviews: number;
	learningMinutes: number;
	recommendations: string[];
	nextWeekPlan: string;
}

export interface ExecutivePlan {
	id: string;
	scopeKey: string;
	executiveEnabled: boolean;
	availableMinutes: number | null;
	agenda: ExecutiveAgenda;
	decisions: ExecutiveDecision[];
	recommendations: ExecutiveRecommendation[];
	weeklyReview: WeeklyExecutiveReview;
}

export interface ExecutiveWatchState {
	objective: string | null;
	priority: ExecutivePriority | null;
	mission: string | null;
	recommendedPause: string | null;
	recommendedReview: string | null;
	nextTopic: string | null;
}

export interface ExecutiveDashboard {
	scopeKey: string;
	plan: ExecutivePlan;
	pendingDecisions: ExecutiveDecision[];
	watch: ExecutiveWatchState;
}

export interface DecisionHistoryStats {
	approved: number;
	rejected: number;
	deferred: number;
	ignored: number;
	pending: number;
}

export interface DecisionHistory {
	scopeKey: string;
	stats: DecisionHistoryStats;
	decisions: ExecutiveDecision[];
}

export interface DecisionEvidenceItem {
	source: EvidenceSource | "unknown";
	reference: string;
	label: string;
}

export interface DecisionExplanation {
	decisionId: string;
	type: DecisionType;
	title: string;
	summary: string;
	reason: ExecutiveReason;
	evidence: DecisionEvidenceItem[];
	impacts: DecisionImpactDimension[];
	linkedGoalId: string | null;
	linkedGoalTitle: string | null;
	linkedConceptKey: string | null;
	linkedResourceId: string | null;
}

export interface ExecutiveAgendaResponse {
	scopeKey: string;
	agenda: ExecutiveAgenda;
}

export interface ExecutiveRecommendationsResponse {
	scopeKey: string;
	recommendations: ExecutiveRecommendation[];
}

export interface ExecutiveDecisionActionRequest {
	scopeKey?: string;
}

export interface ExecutiveResetRequest {
	scopeKey?: string;
}
