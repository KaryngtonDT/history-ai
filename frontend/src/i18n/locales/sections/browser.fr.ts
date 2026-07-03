export const browserFr = {
	browser: {
		title: "Navigateur Shadow",
		description:
			"Configurez le compagnon navigateur Shadow — détection de plateforme, permissions par site, contexte de lecture et explicabilité.",
		whatCanIDo:
			"Connectez la session navigateur, inspectez les plateformes, gérez les permissions par site, consultez l'activité et prévisualisez le contexte du compagnon de lecture.",
		tabs: {
			browser: "Navigateur",
		},
		status: {
			title: "Session navigateur",
			active: "Connecté ({{state}})",
			inactive: "Non connecté",
			connect: "Connecter le navigateur",
			disconnect: "Déconnecter",
			lastActive: "Dernière activité : {{time}}",
		},
		platform: {
			title: "Inspecteur de plateforme",
			urlPlaceholder: "https://www.youtube.com/watch?v=...",
			detect: "Détecter la plateforme",
			result: "{{platform}} détecté sur {{host}}",
		},
		permissions: {
			title: "Centre des permissions",
			siteAllowed: "Site autorisé",
			save: "Enregistrer les permissions",
			noYoutube:
				"Aucune politique youtube.com — enregistrez pour en créer une.",
			allowed: "Autorisé",
			blocked: "Bloqué",
		},
		history: {
			title: "Historique d'activité",
			empty: "Aucune activité navigateur pour l'instant.",
		},
		explain: {
			title: "Pourquoi Shadow a agi ?",
			reason: "Raison : {{reason}}",
			detail: "{{detail}}",
			refresh: "Actualiser l'explication",
		},
		reading: {
			title: "Démo compagnon de lecture",
			description:
				"Simulez une sélection de texte sur une page de lecture. Shadow utilise ce contexte pour poser des questions, rechercher et reprendre.",
			url: "URL de la page",
			pageTitle: "Titre de la page",
			selection: "Texte sélectionné",
			send: "Envoyer le contexte",
			contextPreview:
				"Contexte mis à jour — plateforme : {{platform}}, sélection : « {{selection}} »",
		},
		youtube: {
			title: "Compagnon YouTube",
			description:
				"Pour l'apprentissage vidéo, ouvrez le mode Shadow Watch — le compagnon utilise la transcription et la timeline plutôt que la sélection de page.",
			openWatch: "Ouvrir le mode Shadow Watch",
		},
		extension: {
			title: "Extension navigateur",
			description:
				"Installez l'extension Shadow pour connecter de vrais onglets, capturer la sélection et synchroniser le contexte automatiquement.",
			comingSoon: "Packaging de l'extension dans un sprint futur",
		},
		errors: {
			loadFailed: "Impossible de charger les paramètres navigateur.",
		},
	},
};
