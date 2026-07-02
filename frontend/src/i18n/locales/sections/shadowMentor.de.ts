export const shadowMentorDe = {
	shadowMentor: {
		eyebrow: "Shadow",
		title: "Mentor",
		description:
			"Verfolgen Sie Ihre Transformationsreise: Ziele, Roadmap, Missionen, Fähigkeiten und Wochenreviews.",
		whatCanIDo:
			"Prüfen Sie Ihr Hauptziel, die Mentor-Roadmap, die aktuelle Mission, Meilensteine, Skill-Fortschritt und Zielwirkung.",
		tabs: {
			mentor: "Mentor",
		},
		currentGoal: {
			title: "Aktuelles Ziel",
		},
		roadmap: {
			title: "Roadmap",
		},
		currentMission: {
			title: "Aktuelle Mission",
		},
		nextMilestone: {
			title: "Nächster Meilenstein",
		},
		eta: {
			title: "Voraussichtlicher Abschluss",
			description:
				"Prognostiziertes Datum für Ihr Hauptziel bei aktuellem Tempo.",
			unknown: "Noch nicht geschätzt",
		},
		skills: {
			title: "Skill-Fortschritt",
			percent: "{{percent}} %",
		},
		weeklyReview: {
			title: "Wochenreview",
			progressDelta: "Fortschritt diese Woche: +{{delta}} %",
			milestonesCompleted: "Abgeschlossene Meilensteine: {{count}}",
			adaptationPending: "Plananpassung wartet auf Ihre Freigabe.",
		},
		goalImpact: {
			title: "Zielwirkung",
			percent: "Wirkung: {{percent}} %",
		},
		goal: {
			category: "Kategorie: {{value}}",
			priority: "Priorität: {{value}}",
			progress: "Fortschritt: {{percent}} %",
			deadline: "Frist: {{date}}",
		},
		mission: {
			duration: "{{minutes}} Min.",
			exercises: "Übungen: {{count}}",
		},
		milestone: {
			target: "Zieltermin: {{date}}",
		},
		horizons: {
			today: "Heute",
			week: "Diese Woche",
			month: "Diesen Monat",
			quarter: "Dieses Quartal",
			goal: "Endziel",
		},
		panel: {
			title: "Mentor-Begleiter",
			goal: "Aktuelles Ziel",
			currentMission: "Aktuelle Mission",
			nextMilestone: "Nächster Meilenstein",
			impact: "Zielwirkung",
			impactValue: "{{percent}} %",
		},
		actions: {
			title: "Aktionen",
		},
		reset: {
			action: "Ziele zurücksetzen",
			success: "Ziele und Mentor-Plan zurückgesetzt.",
		},
		empty: {
			goal: "Noch kein Hauptziel festgelegt.",
			roadmap: "Noch keine Roadmap-Schritte.",
			mission: "Keine aktive Mission.",
			milestone: "Kein anstehender Meilenstein.",
			skills: "Noch kein Skill-Fortschritt erfasst.",
			weeklyReview: "Noch kein Wochenreview verfügbar.",
			goalImpact: "Noch keine Zielwirkungsdaten.",
		},
		statusLabel: "{{value}}",
		errors: {
			loadFailed: "Mentor-Dashboard konnte nicht geladen werden.",
			resetFailed: "Ziele konnten nicht zurückgesetzt werden.",
		},
	},
} as const;
