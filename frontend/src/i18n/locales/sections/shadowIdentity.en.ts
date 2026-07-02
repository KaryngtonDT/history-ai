export const shadowIdentityEn = {
	shadowIdentity: {
		eyebrow: "Shadow",
		title: "Shadow Identity",
		description:
			"Customize Shadow's persona, voice, language rules, and teaching style. Every adaptation is explainable and reversible.",
		whatCanIDo:
			"Use Voice Studio, adjust persona and language settings, inspect Shadow DNA, review history, or teach Shadow with natural voice commands.",
		voiceStudio: {
			title: "Voice Studio",
		},
		persona: {
			title: "Persona",
			description: "Choose how Shadow teaches, narrates, and interacts.",
			options: {
				teacher: "Teacher",
				coach: "Coach",
				storyteller: "Storyteller",
				professor: "Professor",
				friendly_companion: "Friendly Companion",
				debater: "Debater",
				socratic_mentor: "Socratic Mentor",
				documentary_narrator: "Documentary Narrator",
				technical_expert: "Technical Expert",
			},
		},
		conversation: {
			title: "Conversation",
			challenge: "Challenge level",
			humor: "Humor",
			examples: "Examples",
		},
		language: {
			title: "Language",
			primary: "Primary language",
			technicalTerms: "Technical terms",
			pronunciation: "Pronunciation",
		},
		memory: {
			title: "Memory",
			interests: "Interests",
			goals: "Goals",
		},
		dna: {
			title: "Shadow DNA",
			curiosity: "Curiosity",
			examples: "Examples",
			stories: "Stories",
			debate: "Debate",
			challenge: "Challenge",
			humor: "Humor",
		},
		history: {
			title: "Configuration history",
		},
		teach: {
			title: "Teach Shadow",
			description:
				"Tell Shadow how to change using natural language. Changes are confirmed and recorded in history.",
			placeholder: "Example: Shadow, speak slower and use a storyteller voice.",
			action: "Teach Shadow",
			confirm: "Confirm change",
		},
		reset: {
			action: "Reset Shadow profile",
			success: "Shadow profile reset.",
		},
		errors: {
			loadFailed: "Unable to load Shadow identity profile.",
			updateFailed: "Unable to update Shadow preferences.",
			resetFailed: "Unable to reset Shadow profile.",
			configureFailed: "Unable to apply conversational configuration.",
		},
	},
} as const;
