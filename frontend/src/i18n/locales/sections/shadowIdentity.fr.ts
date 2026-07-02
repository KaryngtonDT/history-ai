export const shadowIdentityFr = {
	shadowIdentity: {
		eyebrow: "Shadow",
		title: "Identité Shadow",
		description:
			"Personnalisez la persona, la voix, les règles linguistiques et le style pédagogique de Shadow. Chaque adaptation est explicable et réversible.",
		whatCanIDo:
			"Utilisez le studio vocal, ajustez la persona et la langue, consultez l'ADN Shadow, l'historique, ou enseignez Shadow par commandes naturelles.",
		voiceStudio: {
			title: "Studio vocal",
		},
		persona: {
			title: "Persona",
			description: "Choisissez comment Shadow enseigne, narre et interagit.",
			options: {
				teacher: "Professeur",
				coach: "Coach",
				storyteller: "Conteur",
				professor: "Professeur universitaire",
				friendly_companion: "Compagnon amical",
				debater: "Débatteur",
				socratic_mentor: "Mentor socratique",
				documentary_narrator: "Narrateur documentaire",
				technical_expert: "Expert technique",
			},
		},
		conversation: {
			title: "Conversation",
			challenge: "Niveau de défi",
			humor: "Humour",
			examples: "Exemples",
		},
		language: {
			title: "Langue",
			primary: "Langue principale",
			technicalTerms: "Termes techniques",
			pronunciation: "Prononciation",
		},
		memory: {
			title: "Mémoire",
			interests: "Centres d'intérêt",
			goals: "Objectifs",
		},
		dna: {
			title: "ADN Shadow",
			curiosity: "Curiosité",
			examples: "Exemples",
			stories: "Histoires",
			debate: "Débat",
			challenge: "Défi",
			humor: "Humour",
		},
		history: {
			title: "Historique de configuration",
		},
		teach: {
			title: "Enseigner à Shadow",
			description:
				"Dites à Shadow comment changer en langage naturel. Les changements sont confirmés et historisés.",
			placeholder:
				"Exemple : Shadow, parle moins vite et utilise une voix de conteur.",
			action: "Enseigner à Shadow",
			confirm: "Confirmer le changement",
		},
		reset: {
			action: "Réinitialiser le profil Shadow",
			success: "Profil Shadow réinitialisé.",
		},
		errors: {
			loadFailed: "Impossible de charger le profil d'identité Shadow.",
			updateFailed: "Impossible de mettre à jour les préférences Shadow.",
			resetFailed: "Impossible de réinitialiser le profil Shadow.",
			configureFailed:
				"Impossible d'appliquer la configuration conversationnelle.",
		},
	},
} as const;
