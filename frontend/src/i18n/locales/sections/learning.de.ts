export const learningDe = {
	learning: {
		eyebrow: "Adaptive Intelligenz",
		title: "Lernzentrum",
		description:
			"Sehen Sie, wie Lumen kontrolliert und nachvollziehbar aus Shadow, Reviews und Pipeline-Ergebnissen lernt.",
		whatCanIDo:
			"Signale, Insights und Empfehlungen prüfen. Adaptive Hilfe aktivieren oder Lernen jederzeit zurücksetzen.",
		notAvailable: "Nicht verfügbar",
		generatedBecause: "Erzeugt aus {{sources}} Quellsignal(en).",
		recommendationReason: "Basierend auf {{sources}} Quell-Insight(s).",
		errors: {
			loadFailed: "Lernprofil konnte nicht geladen werden.",
			updateFailed: "Lernpräferenzen konnten nicht aktualisiert werden.",
			resetFailed: "Lernprofil konnte nicht zurückgesetzt werden.",
		},
		profile: {
			title: "Lernprofil",
			description:
				"Deterministischer Lernzustand aus recenten Nutzungssignalen.",
			signals: "Signale",
			insights: "Insights",
			recommendations: "Empfehlungen",
			adaptiveStatus: "Adaptiver Modus",
			activeNote:
				"Adaptive Empfehlungen sind aktiv. Shadow und AI Director können weiche Präferenzen anwenden.",
			inactiveNote:
				"Adaptive Empfehlungen sind deaktiviert. Manuelles Verhalten bleibt unverändert.",
		},
		signals: {
			title: "Signal-Zeitleiste",
			description:
				"Append-only Nutzungssignale aus Shadow und Pipeline-Aktivität.",
			empty: "Noch keine Lernsignale erfasst.",
		},
		insights: {
			title: "Insights",
			description: "Deterministische Muster aus erfassten Signalen.",
			empty: "Noch keine Insights erzeugt.",
		},
		recommendations: {
			title: "Empfehlungen",
			description: "Erklärbare Vorschläge aus Insights.",
			empty: "Adaptive Empfehlungen aktivieren, um Vorschläge zu erzeugen.",
		},
		adaptive: {
			toggleLabel: "Adaptive Empfehlungen",
			toggleDescription:
				"Wenn aktiv, können Shadow und AI Director weiche gelernte Präferenzen anwenden.",
			enabled: "Aktiv",
			disabled: "Deaktiviert",
			statusTitle: "Adaptiver Status",
			statusEnabled: "Adaptive Hilfe ist für Shadow und AI Director aktiv.",
			statusDisabled:
				"Adaptive Hilfe ist aus. Bestehende manuelle Einstellungen bleiben erhalten.",
			noAppliedHints: "Derzeit werden keine adaptiven Hinweise angewendet.",
		},
		reset: {
			title: "Lernen zurücksetzen",
			description:
				"Löscht Signale, Insights und Empfehlungen. Präferenzen bleiben erhalten.",
			action: "Lernprofil zurücksetzen",
		},
		sections: {
			shadow: {
				title: "Shadow-Lernen",
				description:
					"Vokabular, Schwierigkeitsgrad, Sprache und Erklärungsstil.",
				vocabularyGaps: "Vokabularlücken",
				challengeLevel: "Schwierigkeitsgrad",
				voiceLanguage: "Sprachsprache",
				explanationStyle: "Erklärungsstil",
			},
			director: {
				title: "AI-Director-Lernen",
				description:
					"Weiche Provider- und Übersetzungsstil-Präferenzen.",
				providerPreference: "Provider-Präferenz",
				translationStyle: "Übersetzungsstil",
			},
		},
	},
	settings: {
		learning: {
			title: "Adaptives Lernen",
			description:
				"Lumens deterministisches Lernprofil ansehen und steuern.",
		},
	},
} as const;
