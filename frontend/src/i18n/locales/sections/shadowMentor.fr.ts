export const shadowMentorFr = {
	shadowMentor: {
		eyebrow: "Shadow",
		title: "Mentor",
		description:
			"Suivez votre parcours de transformation : objectifs, feuille de route, missions, compétences et bilans hebdomadaires.",
		whatCanIDo:
			"Consultez votre objectif principal, la feuille de route mentor, la mission en cours, les jalons, la progression des compétences et l'impact sur vos objectifs.",
		tabs: {
			mentor: "Mentor",
		},
		currentGoal: {
			title: "Objectif actuel",
		},
		roadmap: {
			title: "Feuille de route",
		},
		currentMission: {
			title: "Mission en cours",
		},
		nextMilestone: {
			title: "Prochain jalon",
		},
		eta: {
			title: "Achèvement estimé",
			description:
				"Date prévisionnelle pour atteindre votre objectif principal au rythme actuel.",
			unknown: "Pas encore estimé",
		},
		skills: {
			title: "Progression des compétences",
			percent: "{{percent}} %",
		},
		weeklyReview: {
			title: "Bilan hebdomadaire",
			progressDelta: "Progression cette semaine : +{{delta}} %",
			milestonesCompleted: "Jalons complétés : {{count}}",
			adaptationPending: "Adaptation du plan en attente de votre approbation.",
		},
		goalImpact: {
			title: "Impact sur l'objectif",
			percent: "Impact : {{percent}} %",
		},
		goal: {
			category: "Catégorie : {{value}}",
			priority: "Priorité : {{value}}",
			progress: "Progression : {{percent}} %",
			deadline: "Échéance : {{date}}",
		},
		mission: {
			duration: "{{minutes}} min",
			exercises: "Exercices : {{count}}",
		},
		milestone: {
			target: "Cible : {{date}}",
		},
		horizons: {
			today: "Aujourd'hui",
			week: "Cette semaine",
			month: "Ce mois",
			quarter: "Ce trimestre",
			goal: "Objectif final",
		},
		panel: {
			title: "Compagnon mentor",
			goal: "Objectif actuel",
			currentMission: "Mission en cours",
			nextMilestone: "Prochain jalon",
			impact: "Impact objectif",
			impactValue: "{{percent}} %",
		},
		actions: {
			title: "Actions",
		},
		reset: {
			action: "Réinitialiser les objectifs",
			success: "Objectifs et plan mentor réinitialisés.",
		},
		empty: {
			goal: "Aucun objectif principal défini.",
			roadmap: "Aucune étape de feuille de route.",
			mission: "Aucune mission active.",
			milestone: "Aucun jalon à venir.",
			skills: "Aucune progression de compétence suivie.",
			weeklyReview: "Aucun bilan hebdomadaire disponible.",
			goalImpact: "Aucune donnée d'impact sur les objectifs.",
		},
		statusLabel: "{{value}}",
		errors: {
			loadFailed: "Impossible de charger le tableau de bord mentor.",
			resetFailed: "Impossible de réinitialiser les objectifs.",
		},
	},
} as const;
