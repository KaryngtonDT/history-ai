export const shadowIdentityDe = {
	shadowIdentity: {
		eyebrow: "Shadow",
		title: "Shadow-Identität",
		description:
			"Persona, Stimme, Sprachregeln und Lehrstil von Shadow anpassen. Jede Anpassung ist erklärbar und rückgängig machbar.",
		whatCanIDo:
			"Sprachstudio nutzen, Persona und Sprache anpassen, Shadow-DNA und Verlauf ansehen oder Shadow per natürlicher Sprache konfigurieren.",
		voiceStudio: {
			title: "Sprachstudio",
		},
		persona: {
			title: "Persona",
			description: "Wählen Sie, wie Shadow lehrt, erzählt und interagiert.",
			options: {
				teacher: "Lehrer",
				coach: "Coach",
				storyteller: "Erzähler",
				professor: "Professor",
				friendly_companion: "Freundlicher Begleiter",
				debater: "Debattierer",
				socratic_mentor: "Sokratischer Mentor",
				documentary_narrator: "Dokumentar-Erzähler",
				technical_expert: "Technikexperte",
			},
		},
		conversation: {
			title: "Konversation",
			challenge: "Herausforderungsniveau",
			humor: "Humor",
			examples: "Beispiele",
		},
		language: {
			title: "Sprache",
			primary: "Primärsprache",
			technicalTerms: "Fachbegriffe",
			pronunciation: "Aussprache",
		},
		memory: {
			title: "Gedächtnis",
			interests: "Interessen",
			goals: "Ziele",
		},
		dna: {
			title: "Shadow-DNA",
			curiosity: "Neugier",
			examples: "Beispiele",
			stories: "Geschichten",
			debate: "Debatte",
			challenge: "Herausforderung",
			humor: "Humor",
		},
		history: {
			title: "Konfigurationsverlauf",
		},
		teach: {
			title: "Shadow beibringen",
			description:
				"Sagen Sie Shadow in natürlicher Sprache, was sich ändern soll. Änderungen werden bestätigt und protokolliert.",
			placeholder:
				"Beispiel: Shadow, sprich langsamer und nutze eine Erzählerstimme.",
			action: "Shadow beibringen",
			confirm: "Änderung bestätigen",
		},
		reset: {
			action: "Shadow-Profil zurücksetzen",
			success: "Shadow-Profil zurückgesetzt.",
		},
		errors: {
			loadFailed: "Shadow-Identitätsprofil konnte nicht geladen werden.",
			updateFailed: "Shadow-Einstellungen konnten nicht aktualisiert werden.",
			resetFailed: "Shadow-Profil konnte nicht zurückgesetzt werden.",
			configureFailed:
				"Konversationelle Konfiguration konnte nicht angewendet werden.",
		},
	},
} as const;
