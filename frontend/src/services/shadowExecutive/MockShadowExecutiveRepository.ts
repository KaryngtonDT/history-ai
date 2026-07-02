import type { ShadowExecutiveRepository } from "./ShadowExecutiveRepository";
import type {
	DecisionHistory,
	DecisionHistoryStats,
	ExecutiveAgendaResponse,
	ExecutiveDashboard,
	ExecutiveDecision,
	ExecutivePlan,
	ExecutiveRecommendationsResponse,
} from "./types";

const pendingDecisions: ExecutiveDecision[] = [
	{
		id: "decision-review-docker",
		type: "review",
		status: "pending",
		priority: "high",
		title: "Review Docker fundamentals",
		summary:
			"Refresh container basics before continuing with Kubernetes modules.",
		reason: {
			summary:
				"Kubernetes depends on Docker concepts you last studied 28 days ago.",
			detail:
				"Knowledge graph shows docker_networking and docker_compose at risk of decay. Your mentor mission for Symfony API assumes container fluency.",
		},
		evidence: [
			"knowledge:docker_networking",
			"knowledge:docker_compose",
			"mentor:mission-symfony-kernel",
			"memory:revision_gap_28d",
		],
		impacts: ["knowledge", "goal", "confidence"],
		linkedGoalId: "goal-senior-php",
		linkedConceptKey: "docker_networking",
		linkedResourceId: null,
		constraint: null,
	},
	{
		id: "decision-skip-video-12",
		type: "skip",
		status: "pending",
		priority: "normal",
		title: "Skip advanced Laravel internals video",
		summary:
			"This video does not contribute to your current Symfony-focused mission.",
		reason: {
			summary:
				"Content overlaps with mastered concepts and diverges from primary goal.",
			detail:
				"You completed dependency injection exercises with 92% accuracy. Laravel service container internals are secondary for your Senior PHP goal this week.",
		},
		evidence: [
			"knowledge:dependency_injection",
			"teaching:checkpoint-di-complete",
			"mentor:goal-senior-php",
		],
		impacts: ["time", "goal"],
		linkedGoalId: "goal-senior-php",
		linkedConceptKey: "dependency_injection",
		linkedResourceId: "video-laravel-internals-12",
		constraint: null,
	},
	{
		id: "decision-recommend-pdf-di",
		type: "recommend_pdf",
		status: "pending",
		priority: "normal",
		title: "Read Symfony DI reference (PDF #3)",
		summary:
			"A concise PDF on service wiring will unblock your current mission faster than another video.",
		reason: {
			summary:
				"You learn faster from structured reference material for configuration syntax.",
			detail:
				"Session learning signals show repeated pauses during YAML configuration segments. PDF #3 covers the same patterns with examples.",
		},
		evidence: [
			"session:pause_yaml_segments",
			"teaching:exercise-di-wiring",
			"memory:preferred_pdf_format",
		],
		impacts: ["knowledge", "time", "difficulty"],
		linkedGoalId: "goal-senior-php",
		linkedConceptKey: "symfony_service_container",
		linkedResourceId: "pdf-symfony-di-3",
		constraint: null,
	},
];

const resolvedDecisions: ExecutiveDecision[] = [
	{
		id: "decision-accelerate-di",
		type: "accelerate",
		status: "approved",
		priority: "high",
		title: "Increase DI exercise difficulty",
		summary: "Move to advanced wiring patterns after strong quiz results.",
		reason: {
			summary: "Checkpoint scores exceeded the threshold for acceleration.",
			detail: "Three consecutive DI quizzes scored above 85%.",
		},
		evidence: ["teaching:checkpoint-di-quiz", "knowledge:dependency_injection"],
		impacts: ["difficulty", "confidence"],
		linkedGoalId: "goal-senior-php",
		linkedConceptKey: "dependency_injection",
		linkedResourceId: null,
		constraint: null,
	},
	{
		id: "decision-defer-laravel",
		type: "recommend_mission",
		status: "deferred",
		priority: "low",
		title: "Defer Laravel comparison mission",
		summary:
			"Postpone cross-framework comparison until Symfony kernel is complete.",
		reason: {
			summary: "Focus bandwidth on the active Symfony mission first.",
			detail: "Weekly available minutes are limited to 6 hours.",
		},
		evidence: ["mentor:mission-symfony-kernel", "memory:weekly_hours_6"],
		impacts: ["time", "goal"],
		linkedGoalId: "goal-senior-php",
		linkedConceptKey: "laravel_service_container",
		linkedResourceId: null,
		constraint: null,
	},
	{
		id: "decision-ignore-phpstorm",
		type: "recommend_video",
		status: "ignored",
		priority: "low",
		title: "Watch PHPStorm productivity tips",
		summary: "Optional tooling video — not aligned with current learning path.",
		reason: {
			summary: "Tooling tips do not advance goal-critical concepts.",
			detail: "User chose never suggest again for tooling recommendations.",
		},
		evidence: ["session:tooling_skip_pattern"],
		impacts: ["time"],
		linkedGoalId: null,
		linkedConceptKey: null,
		linkedResourceId: "video-phpstorm-tips",
		constraint: {
			key: "never_tooling_videos",
			label: "Never suggest tooling videos",
			detail: "User ignored this category on 2026-06-28.",
		},
	},
	{
		id: "decision-reject-pause",
		type: "pause",
		status: "rejected",
		priority: "normal",
		title: "Take a learning break today",
		summary: "Suggested pause after three consecutive study days.",
		reason: {
			summary: "Energy-aware planner detected fatigue signals.",
			detail: "Multiple short sessions with low completion rates yesterday.",
		},
		evidence: ["session:fatigue_signals", "memory:study_streak_3d"],
		impacts: ["time", "confidence"],
		linkedGoalId: "goal-senior-php",
		linkedConceptKey: null,
		linkedResourceId: null,
		constraint: null,
	},
];

const defaultPlan: ExecutivePlan = {
	id: "66666666-6666-4666-8666-666666666666",
	scopeKey: "default",
	executiveEnabled: true,
	availableMinutes: 90,
	agenda: {
		today: [
			{
				id: "task-review-docker",
				type: "review",
				label: "Docker networking recap",
				detail: "15-minute spaced revision before Symfony kernel mission.",
				order: 1,
				scheduledAt: "2026-07-02T09:00:00+00:00",
			},
			{
				id: "task-mission-di",
				type: "mission",
				label: "Dependency Injection Foundations",
				detail: "Complete container wiring exercise and checkpoint quiz.",
				order: 2,
				scheduledAt: "2026-07-02T09:20:00+00:00",
			},
			{
				id: "task-watch-symfony",
				type: "watch",
				label: "Symfony kernel request lifecycle",
				detail: "Watch video #7 — map boot sequence and bundle registration.",
				order: 3,
				scheduledAt: "2026-07-02T10:15:00+00:00",
			},
		],
		upcoming: [
			{
				id: "task-exercise-api",
				type: "exercise",
				label: "API validation exercise",
				detail: "Design versioned endpoints with OpenAPI docs.",
				order: 1,
				scheduledAt: "2026-07-03T14:00:00+00:00",
			},
			{
				id: "task-checkpoint-symfony",
				type: "checkpoint",
				label: "Symfony module checkpoint",
				detail: "Boot a custom bundle and expose one tested endpoint.",
				order: 2,
				scheduledAt: "2026-07-05T10:00:00+00:00",
			},
			{
				id: "task-pause-weekend",
				type: "pause",
				label: "Weekend recovery",
				detail: "Light review only — no new missions scheduled.",
				order: 3,
				scheduledAt: "2026-07-06T00:00:00+00:00",
			},
		],
	},
	decisions: [...pendingDecisions, ...resolvedDecisions],
	recommendations: [
		{
			id: "rec-review-docker",
			type: "review",
			title: "Review Docker before Kubernetes",
			detail:
				"Strengthen container networking before advanced orchestration topics.",
			priority: "high",
			conceptKey: "docker_networking",
			resourceId: null,
		},
		{
			id: "rec-skip-laravel",
			type: "skip",
			title: "Skip Laravel internals for now",
			detail: "Focus on Symfony kernel — Laravel comparison can wait.",
			priority: "normal",
			conceptKey: "laravel_service_container",
			resourceId: "video-laravel-internals-12",
		},
		{
			id: "rec-watch-7",
			type: "recommend_video",
			title: "Watch Symfony kernel video #7",
			detail: "Best next step after DI checkpoint completion.",
			priority: "high",
			conceptKey: "symfony_kernel",
			resourceId: "video-symfony-kernel-7",
		},
		{
			id: "rec-pdf-di",
			type: "recommend_pdf",
			title: "Read Symfony DI reference PDF #3",
			detail: "Structured reference for service wiring patterns.",
			priority: "normal",
			conceptKey: "symfony_service_container",
			resourceId: "pdf-symfony-di-3",
		},
	],
	weeklyReview: {
		summary:
			"Strong progress on dependency injection; Docker review is the main gap before Symfony kernel.",
		progressPercent: 34,
		knowledgeGrowth: 12,
		completedMissions: 1,
		missedReviews: 2,
		learningMinutes: 245,
		recommendations: [
			"Prioritize Docker networking review before new Kubernetes content.",
			"Schedule a PHPUnit recap before API design mission.",
			"Keep Symfony-focused path — defer Laravel comparison.",
		],
		nextWeekPlan:
			"Complete Symfony kernel mission, ship one tested endpoint, and start API validation exercises.",
	},
};

function computeHistoryStats(
	decisions: ExecutiveDecision[],
): DecisionHistoryStats {
	const stats: DecisionHistoryStats = {
		approved: 0,
		rejected: 0,
		deferred: 0,
		ignored: 0,
		pending: 0,
	};

	for (const decision of decisions) {
		switch (decision.status) {
			case "approved":
				stats.approved += 1;
				break;
			case "rejected":
				stats.rejected += 1;
				break;
			case "deferred":
				stats.deferred += 1;
				break;
			case "ignored":
				stats.ignored += 1;
				break;
			case "pending":
				stats.pending += 1;
				break;
			default:
				break;
		}
	}

	return stats;
}

function buildDashboard(plan: ExecutivePlan): ExecutiveDashboard {
	const pending = plan.decisions.filter(
		(decision) => decision.status === "pending",
	);
	const todayMission = plan.agenda.today.find(
		(task) => task.type === "mission",
	);
	const todayReview = plan.agenda.today.find((task) => task.type === "review");
	const nextUpcoming = plan.agenda.upcoming[0] ?? null;
	const topPending = pending[0] ?? null;

	return {
		scopeKey: plan.scopeKey,
		plan,
		pendingDecisions: pending,
		watch: {
			objective: todayMission?.label ?? topPending?.title ?? null,
			priority: topPending?.priority ?? null,
			mission: todayMission?.label ?? null,
			recommendedPause: plan.availableMinutes
				? `Plan for ${plan.availableMinutes} minutes today`
				: null,
			recommendedReview: todayReview?.label ?? null,
			nextTopic: nextUpcoming?.label ?? null,
		},
	};
}

export class MockShadowExecutiveRepository
	implements ShadowExecutiveRepository
{
	private plan: ExecutivePlan = defaultPlan;

	getDashboard(): Promise<ExecutiveDashboard> {
		return Promise.resolve(buildDashboard(this.plan));
	}

	getAgenda(): Promise<ExecutiveAgendaResponse> {
		return Promise.resolve({
			scopeKey: this.plan.scopeKey,
			agenda: this.plan.agenda,
		});
	}

	getRecommendations(): Promise<ExecutiveRecommendationsResponse> {
		return Promise.resolve({
			scopeKey: this.plan.scopeKey,
			recommendations: this.plan.recommendations,
		});
	}

	getHistory(): Promise<DecisionHistory> {
		return Promise.resolve({
			scopeKey: this.plan.scopeKey,
			stats: computeHistoryStats(this.plan.decisions),
			decisions: this.plan.decisions.filter(
				(decision) => decision.status !== "pending",
			),
		});
	}

	approveDecision(id: string): Promise<ExecutiveDecision> {
		return this.updateDecisionStatus(id, "approved");
	}

	rejectDecision(id: string): Promise<ExecutiveDecision> {
		return this.updateDecisionStatus(id, "rejected");
	}

	deferDecision(id: string): Promise<ExecutiveDecision> {
		return this.updateDecisionStatus(id, "deferred");
	}

	reset(): Promise<ExecutiveDashboard> {
		this.plan = defaultPlan;

		return Promise.resolve(buildDashboard(this.plan));
	}

	private updateDecisionStatus(
		id: string,
		status: ExecutiveDecision["status"],
	): Promise<ExecutiveDecision> {
		const decision = this.plan.decisions.find((item) => item.id === id);

		if (!decision) {
			return Promise.reject(new Error("Decision not found."));
		}

		if (decision.status !== "pending") {
			return Promise.reject(
				new Error("Only pending decisions can change status."),
			);
		}

		const updated: ExecutiveDecision = {
			...decision,
			status,
		};

		this.plan = {
			...this.plan,
			decisions: this.plan.decisions.map((item) =>
				item.id === id ? updated : item,
			),
		};

		return Promise.resolve(updated);
	}
}
