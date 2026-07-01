export const learningEn = {
	learning: {
		eyebrow: "Adaptive Intelligence",
		title: "Learning Center",
		description:
			"Review how Lumen learns from Shadow, reviews, and pipeline outcomes in a controlled, explainable way.",
		whatCanIDo:
			"Inspect signals, insights, and recommendations. Enable adaptive help or reset learning at any time.",
		notAvailable: "Not available",
		generatedBecause: "Generated from {{sources}} source signal(s).",
		recommendationReason: "Based on {{sources}} source insight(s).",
		errors: {
			loadFailed: "Could not load the learning profile.",
			updateFailed: "Could not update learning preferences.",
			resetFailed: "Could not reset the learning profile.",
		},
		profile: {
			title: "Learning profile",
			description:
				"Deterministic learning state derived from recent usage signals.",
			signals: "Signals",
			insights: "Insights",
			recommendations: "Recommendations",
			adaptiveStatus: "Adaptive mode",
			activeNote:
				"Adaptive recommendations are enabled. Shadow and AI Director may apply soft preferences.",
			inactiveNote:
				"Adaptive recommendations are disabled. Manual behavior stays unchanged.",
		},
		signals: {
			title: "Signal timeline",
			description:
				"Append-only usage signals collected from Shadow and pipeline activity.",
			empty: "No learning signals recorded yet.",
		},
		insights: {
			title: "Insights",
			description: "Deterministic patterns derived from recorded signals.",
			empty: "No insights generated yet.",
		},
		recommendations: {
			title: "Recommendations",
			description: "Explainable suggestions derived from insights.",
			empty: "Enable adaptive recommendations to generate suggestions.",
		},
		adaptive: {
			toggleLabel: "Adaptive recommendations",
			toggleDescription:
				"When enabled, Shadow and AI Director may apply soft learned preferences.",
			enabled: "Enabled",
			disabled: "Disabled",
			statusTitle: "Adaptive status",
			statusEnabled: "Adaptive help is active for Shadow and AI Director.",
			statusDisabled:
				"Adaptive help is off. Existing manual settings are preserved.",
			noAppliedHints: "No adaptive hints are currently applied.",
		},
		reset: {
			title: "Reset learning",
			description:
				"Clear signals, insights, and recommendations. Preferences remain unless you change them.",
			action: "Reset learning profile",
		},
		sections: {
			shadow: {
				title: "Shadow learning",
				description:
					"Vocabulary, challenge level, voice, and explanation preferences.",
				vocabularyGaps: "Vocabulary gaps",
				challengeLevel: "Challenge level",
				voiceLanguage: "Voice language",
				explanationStyle: "Explanation style",
			},
			director: {
				title: "AI Director learning",
				description: "Provider and translation style soft preferences.",
				providerPreference: "Provider preference",
				translationStyle: "Translation style",
			},
		},
	},
} as const;
