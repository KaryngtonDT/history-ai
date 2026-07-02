export const shadowKnowledgeEn = {
	shadowKnowledge: {
		eyebrow: "Shadow",
		title: "Knowledge",
		description:
			"Explore Shadow's knowledge graph: concepts, prerequisites, learning paths, and gaps.",
		whatCanIDo:
			"Inspect concept mastery, search the graph, review learning paths, and rebuild or reset knowledge.",
		tabs: {
			knowledge: "Knowledge",
		},
		overview: {
			title: "Overview",
			nodes: "Concepts",
			edges: "Links",
			readiness: "Goal readiness",
		},
		graph: {
			title: "Knowledge Graph",
		},
		paths: {
			title: "Learning Paths",
		},
		gaps: {
			title: "Learning Gaps",
			goal: "Goal: {{label}}",
			defaultGoal: "Kubernetes",
		},
		search: {
			title: "Search",
			placeholder: "Search concepts or links…",
			results: "{{count}} matches",
		},
		masteryLabel: "Mastery: {{percent}}%",
		selected: "Selected: {{label}}",
		actions: {
			title: "Actions",
			inspect: "Inspect",
		},
		rebuild: {
			action: "Rebuild graph",
			success: "Knowledge graph rebuilt.",
		},
		reset: {
			action: "Reset knowledge",
			success: "Knowledge graph reset.",
		},
		panel: {
			title: "Knowledge Companion",
			goal: "Current goal",
			readiness: "Readiness",
			topGap: "Top gap",
			noGap: "No gaps detected",
			nodes: "Graph nodes",
		},
		empty: {
			gaps: "No learning gaps for this goal.",
		},
		errors: {
			loadFailed: "Could not load knowledge graph.",
			nodeFailed: "Could not load concept details.",
			searchFailed: "Could not search the knowledge graph.",
			rebuildFailed: "Could not rebuild knowledge graph.",
			resetFailed: "Could not reset knowledge graph.",
		},
	},
} as const;
