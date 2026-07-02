import type { ShadowMentorRepository } from "./ShadowMentorRepository";
import type {
	CreateLearningGoalRequest,
	GoalsResponse,
	LearningGoal,
	MentorDashboard,
	MentorMission,
	MissionsResponse,
	RoadmapResponse,
	UpdateLearningGoalRequest,
} from "./types";

const primaryGoal: LearningGoal = {
	id: "goal-senior-php",
	title: "Senior PHP Developer",
	description:
		"Reach senior-level PHP engineering with strong architecture, testing, and framework fluency.",
	motivation:
		"Lead backend projects confidently and mentor junior developers on modern PHP stacks.",
	category: "career",
	priority: "primary",
	status: "active",
	progressPercent: 34,
	deadline: "2026-12-31T00:00:00+00:00",
	targetSkills: [
		"PHP 8",
		"Symfony",
		"Laravel",
		"Dependency Injection",
		"Testing",
		"API Design",
	],
	requiredKnowledge: [
		"dependency_injection",
		"php_generics",
		"symfony_kernel",
		"laravel_service_container",
	],
	successCriteria: [
		"Ship a production Symfony service with tests",
		"Design a REST API with validation and docs",
		"Explain DI trade-offs in code review",
	],
	constraints: [
		{
			key: "weekly_hours",
			label: "Weekly study time",
			detail: "6 hours per week",
		},
	],
};

const missions: MentorMission[] = [
	{
		id: "mission-di-fundamentals",
		goalId: primaryGoal.id,
		title: "Dependency Injection Foundations",
		objective:
			"Wire services through a container and explain constructor vs setter injection.",
		durationMinutes: 45,
		prerequisiteKeys: [],
		exerciseCount: 3,
		validationLabel: "Complete DI checkpoint quiz",
		unlockedConceptKey: "dependency_injection",
		status: "active",
		progressPercent: 55,
	},
	{
		id: "mission-symfony-kernel",
		goalId: primaryGoal.id,
		title: "Symfony Kernel & Bundles",
		objective: "Trace request lifecycle and register a custom bundle.",
		durationMinutes: 60,
		prerequisiteKeys: ["dependency_injection"],
		exerciseCount: 2,
		validationLabel: "Boot a custom bundle in dev",
		unlockedConceptKey: "symfony_kernel",
		status: "upcoming",
		progressPercent: 0,
	},
	{
		id: "mission-api-design",
		goalId: primaryGoal.id,
		title: "Production API Design",
		objective: "Design versioned endpoints with validation and OpenAPI docs.",
		durationMinutes: 90,
		prerequisiteKeys: ["symfony_kernel"],
		exerciseCount: 4,
		validationLabel: "Publish OpenAPI spec for a sample module",
		unlockedConceptKey: "api_design",
		status: "upcoming",
		progressPercent: 0,
	},
];

const defaultDashboard: MentorDashboard = {
	scopeKey: "default",
	primaryGoal,
	plan: {
		id: "55555555-5555-4555-8555-555555555555",
		scopeKey: "default",
		mentorEnabled: true,
		missions,
		roadmap: [
			{
				horizon: "today",
				label: "DI constructor patterns",
				detail: "Finish container wiring exercise and review Symfony docs.",
				order: 1,
			},
			{
				horizon: "week",
				label: "Symfony request lifecycle",
				detail: "Map kernel boot sequence and bundle registration.",
				order: 2,
			},
			{
				horizon: "month",
				label: "Testing strategy",
				detail: "PHPUnit + integration tests for a service module.",
				order: 3,
			},
			{
				horizon: "quarter",
				label: "Lead a backend feature",
				detail: "Own API design, review, and deployment checklist.",
				order: 4,
			},
			{
				horizon: "goal",
				label: "Senior PHP Developer",
				detail: "Demonstrate architecture decisions in a portfolio project.",
				order: 5,
			},
		],
		skills: [
			{ key: "php_core", label: "PHP Core", percent: 62 },
			{ key: "symfony", label: "Symfony", percent: 28 },
			{ key: "laravel", label: "Laravel", percent: 41 },
			{ key: "testing", label: "Testing", percent: 36 },
			{ key: "api_design", label: "API Design", percent: 22 },
		],
		milestones: [
			{
				id: "milestone-di",
				goalId: primaryGoal.id,
				label: "DI checkpoint",
				detail:
					"Explain and implement constructor injection in a sample service.",
				completed: false,
				targetAt: "2026-07-15T00:00:00+00:00",
				completedAt: null,
			},
			{
				id: "milestone-symfony",
				goalId: primaryGoal.id,
				label: "Symfony module shipped",
				detail: "Register a bundle and expose one tested endpoint.",
				completed: false,
				targetAt: "2026-09-01T00:00:00+00:00",
				completedAt: null,
			},
		],
		currentMissionId: "mission-di-fundamentals",
		estimatedCompletionAt: "2026-11-30T00:00:00+00:00",
		weeklyReview: {
			summary:
				"Solid progress on dependency injection; Symfony kernel is the next focus.",
			progressDelta: 6,
			milestonesCompleted: 0,
			difficultyNote:
				"Container configuration syntax slowed exercise completion.",
			recommendations: [
				"Add a 20-minute Symfony kernel primer before the next mission.",
				"Schedule a PHPUnit recap before API design.",
			],
			adaptationPending: true,
			generatedAt: "2026-07-01T18:00:00+00:00",
		},
	},
	currentMission: missions[0],
	nextMilestone: {
		id: "milestone-di",
		goalId: primaryGoal.id,
		label: "DI checkpoint",
		detail: "Explain and implement constructor injection in a sample service.",
		completed: false,
		targetAt: "2026-07-15T00:00:00+00:00",
		completedAt: null,
	},
	goalImpact: [
		{
			goalId: primaryGoal.id,
			goalTitle: primaryGoal.title,
			impactPercent: 72,
			reason:
				"This session reinforces dependency injection for your primary goal.",
		},
	],
};

export class MockShadowMentorRepository implements ShadowMentorRepository {
	private dashboard: MentorDashboard = defaultDashboard;

	getDashboard(): Promise<MentorDashboard> {
		return Promise.resolve(this.dashboard);
	}

	getGoals(): Promise<GoalsResponse> {
		return Promise.resolve({
			scopeKey: this.dashboard.scopeKey,
			goals: this.dashboard.primaryGoal ? [this.dashboard.primaryGoal] : [],
			primaryGoal: this.dashboard.primaryGoal,
		});
	}

	createGoal(request: CreateLearningGoalRequest): Promise<LearningGoal> {
		const goal: LearningGoal = {
			id: `goal-${Date.now()}`,
			title: request.title,
			description: request.description ?? "",
			motivation: request.motivation ?? "",
			category: request.category ?? "custom",
			priority: request.priority ?? "secondary",
			status: "active",
			progressPercent: 0,
			deadline: null,
			targetSkills: [],
			requiredKnowledge: [],
			successCriteria: [],
			constraints: [],
		};

		this.dashboard = {
			...this.dashboard,
			primaryGoal: goal,
		};

		return Promise.resolve(goal);
	}

	updateGoal(
		id: string,
		request: UpdateLearningGoalRequest,
	): Promise<LearningGoal> {
		if (!this.dashboard.primaryGoal || this.dashboard.primaryGoal.id !== id) {
			return Promise.reject(new Error("Goal not found."));
		}

		const updated: LearningGoal = {
			...this.dashboard.primaryGoal,
			title: request.title ?? this.dashboard.primaryGoal.title,
			description:
				request.description ?? this.dashboard.primaryGoal.description,
			motivation: request.motivation ?? this.dashboard.primaryGoal.motivation,
			category: request.category ?? this.dashboard.primaryGoal.category,
			priority: request.priority ?? this.dashboard.primaryGoal.priority,
			status: request.status ?? this.dashboard.primaryGoal.status,
			progressPercent:
				request.progressPercent ?? this.dashboard.primaryGoal.progressPercent,
			deadline:
				request.deadline !== undefined
					? request.deadline
					: this.dashboard.primaryGoal.deadline,
			targetSkills:
				request.targetSkills ?? this.dashboard.primaryGoal.targetSkills,
			requiredKnowledge:
				request.requiredKnowledge ??
				this.dashboard.primaryGoal.requiredKnowledge,
			successCriteria:
				request.successCriteria ?? this.dashboard.primaryGoal.successCriteria,
			constraints: this.dashboard.primaryGoal.constraints,
		};

		this.dashboard = {
			...this.dashboard,
			primaryGoal: updated,
		};

		return Promise.resolve(updated);
	}

	deleteGoal(id: string): Promise<void> {
		if (this.dashboard.primaryGoal?.id === id) {
			this.dashboard = {
				...this.dashboard,
				primaryGoal: null,
			};
		}

		return Promise.resolve();
	}

	getMissions(): Promise<MissionsResponse> {
		return Promise.resolve({
			scopeKey: this.dashboard.scopeKey,
			missions: this.dashboard.plan.missions,
			currentMission: this.dashboard.currentMission,
		});
	}

	getRoadmap(): Promise<RoadmapResponse> {
		return Promise.resolve({
			scopeKey: this.dashboard.scopeKey,
			roadmap: this.dashboard.plan.roadmap,
		});
	}

	completeMission(missionId: string): Promise<MentorMission> {
		const mission = this.dashboard.plan.missions.find(
			(item) => item.id === missionId,
		);

		if (!mission) {
			return Promise.reject(new Error("Mission not found."));
		}

		const completed: MentorMission = {
			...mission,
			status: "completed",
			progressPercent: 100,
		};

		const missions = this.dashboard.plan.missions.map((item) =>
			item.id === missionId ? completed : item,
		);

		this.dashboard = {
			...this.dashboard,
			plan: {
				...this.dashboard.plan,
				missions,
				currentMissionId: null,
			},
			currentMission: null,
		};

		return Promise.resolve(completed);
	}

	resetGoals(): Promise<GoalsResponse> {
		this.dashboard = defaultDashboard;

		return Promise.resolve({
			scopeKey: this.dashboard.scopeKey,
			goals: this.dashboard.primaryGoal ? [this.dashboard.primaryGoal] : [],
			primaryGoal: this.dashboard.primaryGoal,
		});
	}
}
