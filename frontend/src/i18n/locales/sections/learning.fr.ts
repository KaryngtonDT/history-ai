export const learningFr = {
	learning: {
		eyebrow: "Intelligence adaptive",
		title: "Centre d'apprentissage",
		description:
			"Consultez comment Lumen apprend de Shadow, des avis et des résultats de pipeline de manière contrôlée et explicable.",
		whatCanIDo:
			"Inspectez signaux, insights et recommandations. Activez l'aide adaptive ou réinitialisez l'apprentissage à tout moment.",
		notAvailable: "Non disponible",
		generatedBecause: "Généré à partir de {{sources}} signal(aux) source.",
		recommendationReason: "Basé sur {{sources}} insight(s) source.",
		errors: {
			loadFailed: "Impossible de charger le profil d'apprentissage.",
			updateFailed:
				"Impossible de mettre à jour les préférences d'apprentissage.",
			resetFailed: "Impossible de réinitialiser le profil d'apprentissage.",
		},
		profile: {
			title: "Profil d'apprentissage",
			description:
				"État d'apprentissage déterministe dérivé des signaux d'usage récents.",
			signals: "Signaux",
			insights: "Insights",
			recommendations: "Recommandations",
			adaptiveStatus: "Mode adaptatif",
			activeNote:
				"Les recommandations adaptatives sont activées. Shadow et AI Director peuvent appliquer des préférences douces.",
			inactiveNote:
				"Les recommandations adaptatives sont désactivées. Le comportement manuel reste inchangé.",
		},
		signals: {
			title: "Chronologie des signaux",
			description:
				"Signaux d'usage append-only collectés depuis Shadow et l'activité pipeline.",
			empty: "Aucun signal d'apprentissage enregistré pour l'instant.",
		},
		insights: {
			title: "Insights",
			description: "Motifs déterministes dérivés des signaux enregistrés.",
			empty: "Aucun insight généré pour l'instant.",
		},
		recommendations: {
			title: "Recommandations",
			description: "Suggestions explicables dérivées des insights.",
			empty:
				"Activez les recommandations adaptatives pour générer des suggestions.",
		},
		adaptive: {
			toggleLabel: "Recommandations adaptatives",
			toggleDescription:
				"Lorsqu'activé, Shadow et AI Director peuvent appliquer des préférences apprises douces.",
			enabled: "Activé",
			disabled: "Désactivé",
			statusTitle: "Statut adaptatif",
			statusEnabled: "L'aide adaptive est active pour Shadow et AI Director.",
			statusDisabled:
				"L'aide adaptive est désactivée. Les réglages manuels existants sont conservés.",
			noAppliedHints: "Aucun hint adaptatif n'est appliqué actuellement.",
		},
		reset: {
			title: "Réinitialiser l'apprentissage",
			description:
				"Efface signaux, insights et recommandations. Les préférences restent sauf modification.",
			action: "Réinitialiser le profil d'apprentissage",
		},
		sections: {
			shadow: {
				title: "Apprentissage Shadow",
				description:
					"Vocabulaire, niveau de défi, voix et style d'explication.",
				vocabularyGaps: "Lacunes de vocabulaire",
				challengeLevel: "Niveau de défi",
				voiceLanguage: "Langue vocale",
				explanationStyle: "Style d'explication",
			},
			director: {
				title: "Apprentissage AI Director",
				description:
					"Préférences douces de provider et de style de traduction.",
				providerPreference: "Préférence de provider",
				translationStyle: "Style de traduction",
			},
		},
	},
} as const;
