export const shadowMentorEn = {
	shadowMentor: {
		eyebrow: "Shadow",
		title: "Mentor",
		description:
			"Follow your transformation journey: goals, roadmap, missions, skills, and weekly reviews.",
		whatCanIDo:
			"Review your primary goal, mentor roadmap, current mission, milestones, skill progress, and goal impact.",
		tabs: {
			mentor: "Mentor",
		},
		currentGoal: {
			title: "Current Goal",
		},
		roadmap: {
			title: "Roadmap",
		},
		currentMission: {
			title: "Current Mission",
		},
		nextMilestone: {
			title: "Next Milestone",
		},
		eta: {
			title: "Estimated Completion",
			description: "Projected date to reach your primary goal at current pace.",
			unknown: "Not estimated yet",
		},
		skills: {
			title: "Skills Progress",
			percent: "{{percent}}%",
		},
		weeklyReview: {
			title: "Weekly Review",
			progressDelta: "Progress this week: +{{delta}}%",
			milestonesCompleted: "Milestones completed: {{count}}",
			adaptationPending: "Plan adaptation pending your approval.",
		},
		goalImpact: {
			title: "Goal Impact",
			percent: "Impact: {{percent}}%",
		},
		goal: {
			category: "Category: {{value}}",
			priority: "Priority: {{value}}",
			progress: "Progress: {{percent}}%",
			deadline: "Deadline: {{date}}",
		},
		mission: {
			duration: "{{minutes}} min",
			exercises: "Exercises: {{count}}",
		},
		milestone: {
			target: "Target: {{date}}",
		},
		horizons: {
			today: "Today",
			week: "This week",
			month: "This month",
			quarter: "This quarter",
			goal: "Final goal",
		},
		panel: {
			title: "Mentor Companion",
			goal: "Current goal",
			currentMission: "Current mission",
			nextMilestone: "Next milestone",
			impact: "Goal impact",
			impactValue: "{{percent}}%",
		},
		actions: {
			title: "Actions",
		},
		reset: {
			action: "Reset goals",
			success: "Goals and mentor plan reset.",
		},
		empty: {
			goal: "No primary goal set yet.",
			roadmap: "No roadmap steps yet.",
			mission: "No active mission.",
			milestone: "No upcoming milestone.",
			skills: "No skill progress tracked yet.",
			weeklyReview: "No weekly review available yet.",
			goalImpact: "No goal impact data yet.",
		},
		statusLabel: "{{value}}",
		errors: {
			loadFailed: "Could not load mentor dashboard.",
			resetFailed: "Could not reset goals.",
		},
	},
} as const;
