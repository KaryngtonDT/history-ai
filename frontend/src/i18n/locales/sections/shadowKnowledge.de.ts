export const shadowKnowledgeDe = {
	shadowKnowledge: {
		eyebrow: "Shadow",
		title: "Wissen",
		description:
			"Erkunde Shadows Wissensgraph: Konzepte, Voraussetzungen, Lernpfade und Lücken.",
		whatCanIDo:
			"Prüfe Konzeptbeherrschung, durchsuche den Graphen, sieh Lernpfade ein und baue Wissen neu auf oder setze es zurück.",
		tabs: {
			knowledge: "Wissen",
		},
		overview: {
			title: "Überblick",
			nodes: "Konzepte",
			edges: "Verknüpfungen",
			readiness: "Zielbereitschaft",
		},
		graph: {
			title: "Wissensgraph",
		},
		paths: {
			title: "Lernpfade",
		},
		gaps: {
			title: "Lernlücken",
			goal: "Ziel: {{label}}",
			defaultGoal: "Kubernetes",
		},
		search: {
			title: "Suche",
			placeholder: "Konzepte oder Verknüpfungen suchen…",
			results: "{{count}} Treffer",
		},
		masteryLabel: "Beherrschung: {{percent}} %",
		selected: "Ausgewählt: {{label}}",
		actions: {
			title: "Aktionen",
			inspect: "Ansehen",
		},
		rebuild: {
			action: "Graphen neu aufbauen",
			success: "Wissensgraph neu aufgebaut.",
		},
		reset: {
			action: "Wissen zurücksetzen",
			success: "Wissensgraph zurückgesetzt.",
		},
		panel: {
			title: "Wissens-Begleiter",
			goal: "Aktuelles Ziel",
			readiness: "Bereitschaft",
			topGap: "Wichtigste Lücke",
			noGap: "Keine Lücken erkannt",
			nodes: "Graph-Knoten",
		},
		empty: {
			gaps: "Keine Lernlücken für dieses Ziel.",
		},
		errors: {
			loadFailed: "Wissensgraph konnte nicht geladen werden.",
			nodeFailed: "Konzeptdetails konnten nicht geladen werden.",
			searchFailed: "Suche im Wissensgraph fehlgeschlagen.",
			rebuildFailed: "Wissensgraph konnte nicht neu aufgebaut werden.",
			resetFailed: "Wissensgraph konnte nicht zurückgesetzt werden.",
		},
	},
} as const;
