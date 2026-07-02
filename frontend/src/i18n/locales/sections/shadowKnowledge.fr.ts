export const shadowKnowledgeFr = {
	shadowKnowledge: {
		eyebrow: "Shadow",
		title: "Connaissances",
		description:
			"Explorez le graphe de connaissances de Shadow : concepts, prérequis, parcours et lacunes.",
		whatCanIDo:
			"Inspectez la maîtrise des concepts, recherchez dans le graphe, consultez les parcours et reconstruisez ou réinitialisez les connaissances.",
		tabs: {
			knowledge: "Connaissances",
		},
		overview: {
			title: "Vue d'ensemble",
			nodes: "Concepts",
			edges: "Liens",
			readiness: "Préparation à l'objectif",
		},
		graph: {
			title: "Graphe de connaissances",
		},
		paths: {
			title: "Parcours d'apprentissage",
		},
		gaps: {
			title: "Lacunes d'apprentissage",
			goal: "Objectif : {{label}}",
			defaultGoal: "Kubernetes",
		},
		search: {
			title: "Recherche",
			placeholder: "Rechercher des concepts ou des liens…",
			results: "{{count}} résultats",
		},
		masteryLabel: "Maîtrise : {{percent}} %",
		selected: "Sélectionné : {{label}}",
		actions: {
			title: "Actions",
			inspect: "Inspecter",
		},
		rebuild: {
			action: "Reconstruire le graphe",
			success: "Graphe de connaissances reconstruit.",
		},
		reset: {
			action: "Réinitialiser les connaissances",
			success: "Graphe de connaissances réinitialisé.",
		},
		panel: {
			title: "Compagnon connaissances",
			goal: "Objectif actuel",
			readiness: "Préparation",
			topGap: "Lacune principale",
			noGap: "Aucune lacune détectée",
			nodes: "Nœuds du graphe",
		},
		empty: {
			gaps: "Aucune lacune pour cet objectif.",
		},
		errors: {
			loadFailed: "Impossible de charger le graphe de connaissances.",
			nodeFailed: "Impossible de charger les détails du concept.",
			searchFailed: "Impossible de rechercher dans le graphe.",
			rebuildFailed: "Impossible de reconstruire le graphe.",
			resetFailed: "Impossible de réinitialiser le graphe.",
		},
	},
} as const;
