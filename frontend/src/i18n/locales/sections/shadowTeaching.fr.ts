export const shadowTeachingFr = {
	shadowTeaching: {
		eyebrow: "Shadow",
		title: "Enseignement",
		description:
			"Suivez le plan pédagogique de Shadow pour votre niveau actuel : missions, leçons, exercices et révisions.",
		whatCanIDo:
			"Consultez le parcours d'apprentissage, les priorités de la leçon en cours, le mode vocal et réinitialisez le plan pédagogique.",
		tabs: {
			teaching: "Enseignement",
		},
		learningPath: {
			title: "Parcours d'apprentissage",
		},
		currentLesson: {
			title: "Leçon en cours",
			exercisesDue: "Exercices à faire : {{count}}",
			revisionDue: "Révisions à faire : {{count}}",
		},
		objectives: {
			title: "Objectifs",
		},
		exercises: {
			title: "Exercices",
		},
		revisionQueue: {
			title: "File de révision",
		},
		progress: {
			title: "Progression",
			objectives: "Objectifs",
			exercises: "Exercices",
			checkpoints: "Jalons",
		},
		history: {
			title: "Historique",
		},
		preferences: {
			title: "Préférences",
			voiceMode: "Mode vocal",
			saved: "Préférences pédagogiques enregistrées.",
			modes: {
				coach: "Coach",
				mentor: "Mentor",
				story: "Narratif",
			},
		},
		panel: {
			title: "Compagnon pédagogique",
			todayLesson: "Leçon du jour",
			nextCheckpoint: "Prochain jalon",
			noneCheckpoint: "Aucun jalon en attente",
			exercisesCount: "Exercices en attente",
			revisionReminder: "Rappel de révision",
		},
		reset: {
			action: "Réinitialiser l'enseignement",
			success: "Plan pédagogique réinitialisé.",
		},
		empty: {
			currentLesson: "Aucune leçon sélectionnée.",
		},
		statusLabel: "{{value}}",
		errors: {
			loadFailed: "Impossible de charger le plan pédagogique.",
			preferencesFailed:
				"Impossible de mettre à jour les préférences pédagogiques.",
			resetFailed: "Impossible de réinitialiser le plan pédagogique.",
		},
	},
} as const;
