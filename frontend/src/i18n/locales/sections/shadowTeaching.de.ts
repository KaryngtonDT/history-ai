export const shadowTeachingDe = {
	shadowTeaching: {
		eyebrow: "Shadow",
		title: "Unterricht",
		description:
			"Verfolgen Sie Shadows Unterrichtsplan fur Ihre aktuelle Lernphase: Missionen, Lektionen, Ubungen und Wiederholungen.",
		whatCanIDo:
			"Lernpfad ansehen, Prioritaten der aktuellen Lektion prufen, Sprachmodus steuern und den Unterrichtsplan zurucksetzen.",
		tabs: {
			teaching: "Unterricht",
		},
		learningPath: {
			title: "Lernpfad",
		},
		currentLesson: {
			title: "Aktuelle Lektion",
			exercisesDue: "Fallige Ubungen: {{count}}",
			revisionDue: "Fallige Wiederholung: {{count}}",
		},
		objectives: {
			title: "Ziele",
		},
		exercises: {
			title: "Ubungen",
		},
		revisionQueue: {
			title: "Wiederholungswarteschlange",
		},
		progress: {
			title: "Fortschritt",
			objectives: "Ziele",
			exercises: "Ubungen",
			checkpoints: "Checkpoints",
		},
		history: {
			title: "Verlauf",
		},
		preferences: {
			title: "Einstellungen",
			voiceMode: "Sprachmodus",
			saved: "Unterrichtseinstellungen gespeichert.",
			modes: {
				coach: "Coach",
				mentor: "Mentor",
				story: "Story",
			},
		},
		panel: {
			title: "Unterrichts-Begleiter",
			todayLesson: "Heutige Lektion",
			nextCheckpoint: "Nachster Checkpoint",
			noneCheckpoint: "Kein Checkpoint ausstehend",
			exercisesCount: "Ausstehende Ubungen",
			revisionReminder: "Wiederholungs-Erinnerung",
		},
		reset: {
			action: "Unterricht zurucksetzen",
			success: "Unterrichtsplan zuruckgesetzt.",
		},
		empty: {
			currentLesson: "Noch keine Lektion ausgewahlt.",
		},
		statusLabel: "{{value}}",
		errors: {
			loadFailed: "Unterrichtsplan konnte nicht geladen werden.",
			preferencesFailed:
				"Unterrichtseinstellungen konnten nicht aktualisiert werden.",
			resetFailed: "Unterrichtsplan konnte nicht zuruckgesetzt werden.",
		},
	},
} as const;
