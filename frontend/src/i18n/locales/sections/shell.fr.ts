import type { DeepStringRecord } from "../../localeUtils";
import type { shellEn } from "./shell.en";

type ShellMessages = DeepStringRecord<typeof shellEn>;

export const shellFr: ShellMessages = {
	shell: {
		brand: {
			title: "Lumen",
			subtitle:
				"Plateforme IA pour transformer vos contenus en connaissance et compréhension.",
			tagline: "Compréhension guidée par l'IA",
		},
		sidebar: {
			ariaLabel: "Navigation produit",
			shortcut: "Appuyez sur {{modifier}}+K pour rechercher partout",
		},
		breadcrumbs: {
			ariaLabel: "Fil d'Ariane",
			home: "Accueil",
			import: "Importer",
			video: "Vidéo",
			upload: "Téléverser",
			transcript: "Transcription",
			translations: "Traductions",
			audio: "Audio",
			youtube: "YouTube",
			"voice-clone": "Voix clonée",
			"lip-sync": "Aperçu lip sync",
			render: "Vidéo finale",
			watch: "Shadow",
			workspace: "Espace de travail",
			library: "Bibliothèque",
			collections: "Collections",
			settings: "Paramètres",
			ai: "Modèles IA",
			pipeline: "Configuration du pipeline",
			processing: "Traitement",
			overview: "Vue d'ensemble",
			videoId: "Vidéo {{id}}…",
		},
		pageIntro: {
			whatCanIDo: "Que puis-je faire ici ?",
		},
		nav: {
			groups: {
				create: "Créer",
				pipeline: "Pipeline IA",
				results: "Résultats",
				library: "Bibliothèque",
				settings: "Paramètres",
			},
			items: {
				dashboard: {
					label: "Accueil",
					description: "Orientation, création et travaux récents.",
				},
				upload: {
					label: "Téléverser une vidéo",
					description: "Téléversez une vidéo et lancez le pipeline IA.",
				},
				import: {
					label: "Importer des documents",
					description:
						"Importez des PDF ou de l'audio pour le traitement de connaissance.",
				},
				workspace: {
					label: "Espace de travail",
					description: "Projets, traitement par lot, équipe et analytique.",
				},
				"pipeline-settings": {
					label: "Configuration du pipeline",
					description: "Choisissez les moteurs IA par étape.",
				},
				"runtime-settings": {
					label: "Centre Runtime",
					description: "Découvrir, vérifier et benchmarker les moteurs IA.",
				},
				"ai-engines": {
					label: "Modèles IA",
					description:
						"Consultez les fournisseurs et capacités IA disponibles.",
				},
				transcript: { label: "Transcription" },
				translations: { label: "Traductions" },
				audio: { label: "Audio" },
				"voice-clone": { label: "Voix clonée" },
				"lip-sync": { label: "Aperçu lip sync" },
				render: { label: "Vidéo finale" },
				shadow: { label: "Shadow" },
				library: { label: "Bibliothèque" },
				collections: { label: "Collections" },
				"settings-hub": {
					label: "Paramètres",
					description: "Configuration de l'application et du pipeline IA.",
				},
			},
			empty: {
				transcript: {
					reason: "Pas encore de transcription.",
					why: "La transcription débloque traduction, audio et recherche de connaissances.",
					action: "Téléverser une vidéo",
				},
				translations: {
					reason: "Pas encore de traductions.",
					why: "Les traductions adaptent votre contenu à chaque langue cible.",
					action: "Ouvrir une vidéo",
				},
				audio: {
					reason: "Pas encore d'audio généré.",
					why: "La parole générée alimente le clonage vocal et le lip sync.",
					action: "Ouvrir une vidéo",
				},
				"voice-clone": {
					reason: "Pas encore de voix clonée.",
					why: "La voix clonée conserve l'identité du locuteur original.",
					action: "Générer l'audio d'abord",
				},
				"lip-sync": {
					reason: "Pas encore d'aperçu lip sync.",
					why: "Le lip sync aligne les mouvements des lèvres avant le rendu final.",
					action: "Compléter les étapes précédentes",
				},
				render: {
					reason: "Pas encore de vidéo finale.",
					why: "Le rendu final produit un MP4 multilingue téléchargeable.",
					action: "Lancer le pipeline",
				},
				shadow: {
					reason: "Session Shadow non démarrée.",
					why: "Shadow aide à apprendre en regardant avec des Q&R contextuelles.",
					action: "Ouvrir une vidéo",
				},
			},
		},
	},
	home: {
		eyebrow: "Accueil",
		title: "Lumen",
		description: "Transformer la connaissance en compréhension.",
		whatCanIDo:
			"Téléversez une vidéo, importez un document ou reprenez un travail en cours.",
		loading: "Chargement de l'accueil",
		loadError:
			"Impossible de charger vos travaux récents. Vérifiez que le backend est démarré.",
		errorTitle: "Impossible de charger l'accueil",
		create: {
			heading: "Que souhaitez-vous transformer ?",
			nextPrefix: "Suivant :",
			ariaLabel: "Créer {{type}}",
			video: {
				label: "Vidéo",
				description:
					"Téléversez et traduisez une vidéo avec voix IA, lip sync et rendu.",
				nextStep:
					"Choisissez les langues et le mode IA après le téléversement.",
			},
			pdf: {
				label: "PDF",
				description:
					"Importez des documents pour l'extraction de connaissance et le chat.",
				nextStep:
					"Le traitement crée des résumés, un graphe et des éléments de bibliothèque.",
			},
			audio: {
				label: "Audio",
				description:
					"Importez de l'audio pour générer des transcriptions et des insights.",
				nextStep: "La transcription sera disponible pour révision.",
			},
			youtube: {
				label: "YouTube",
				description: "Liez une source YouTube pour traitement.",
				nextStep: "Exécute le pipeline complet de traitement vidéo.",
			},
		},
		continue: {
			heading: "Reprendre votre travail",
			currentStep: "Étape actuelle :",
		},
		recent: {
			heading: "Travaux récents",
			empty:
				"Aucun travail pour l'instant. Téléversez une vidéo ou importez un document pour commencer.",
			open: "Ouvrir →",
			openAria: "Ouvrir {{title}}",
		},
		stats: {
			heading: "En un coup d'œil",
			videos: {
				label: "Vidéos",
				description: "Travaux vidéo actifs",
				action: "Voir l'espace de travail →",
			},
			projects: {
				label: "Projets",
				description: "Projets par lot et en équipe",
				action: "Ouvrir les projets →",
			},
			completed: {
				label: "Terminés",
				description: "Éléments de travail finis",
				action: "Ouvrir la bibliothèque →",
			},
			artifacts: {
				label: "Artefacts",
				description: "Sorties générées",
				action: "Parcourir les artefacts →",
			},
			ariaLabel: "{{label}} : {{count}}. {{action}}",
		},
		aiDirector: {
			heading: "Directeur IA",
			loading: "Chargement de la recommandation",
			recommended: "Workflow recommandé :",
			configureLink: "Configurer au téléversement →",
			empty:
				"Téléversez une vidéo en mode automatique pour voir les recommandations du pipeline IA.",
			uploadLink: "Téléverser une vidéo →",
		},
	},
	guidance: {
		palette: {
			closeAria: "Fermer la palette de commandes",
			dialogAria: "Palette de commandes",
			placeholder: "Rechercher vidéos, projets, pipeline, analytique…",
			empty: "Aucune commande correspondante.",
			footer: "{{count}} commandes · Échap pour fermer",
			groups: {
				Navigate: "Navigation",
				Create: "Créer",
				Results: "Résultats",
				Settings: "Paramètres",
				Library: "Bibliothèque",
				Review: "Revue",
			},
		},
		commands: {
			dashboard: {
				label: "Accueil",
				description: "Orientation et travaux récents",
			},
			upload: {
				label: "Téléverser une vidéo",
				description: "Démarrer le pipeline de traitement vidéo par l'IA",
			},
			workspace: {
				label: "Espace de travail",
				description: "Projets, lot, équipe, analytique",
			},
			transcript: {
				label: "Ouvrir la transcription",
				description: "Nécessite une vidéo dans le contexte URL",
			},
			translations: {
				label: "Ouvrir les traductions",
				description: "Voir les langues traduites",
			},
			audio: {
				label: "Ouvrir l'audio",
				description: "Prévisualiser l'audio généré",
			},
			pipeline: {
				label: "Configuration du pipeline",
				description: "Configurer les moteurs IA par étape",
			},
			ai: {
				label: "Moteurs IA",
				description: "Voir les fournisseurs disponibles",
			},
			library: {
				label: "Bibliothèque",
				description: "Parcourir le contenu enregistré",
			},
			analytics: {
				label: "Analytique de l'espace de travail",
				description: "Télémétrie et statistiques des fournisseurs",
			},
			import: {
				label: "Importer des documents",
				description: "Import PDF et audio",
			},
		},
	},
	settings: {
		eyebrow: "Paramètres",
		title: "Paramètres",
		description: "Configurez comment Lumen traite vos vidéos.",
		whatCanIDo:
			"Choisissez les moteurs IA et les étapes du pipeline. Les changements s'appliquent au prochain traitement.",
		language: {
			title: "Langue de l'interface",
			description: "Choisissez la langue des menus, libellés et textes d'aide.",
		},
		runtime: {
			title: "Runtime IA",
			description:
				"Découvrir, vérifier, benchmarker et valider les moteurs IA installés.",
			analytics: {
				title: "Analytique de performance des moteurs",
				description:
					"Comparer l'historique d'exécution réel, la précision des estimations et la vitesse relative par moteur.",
				whatCanIDo:
					"Examinez quels moteurs sont les plus rapides sur votre profil matériel selon les jobs de pipeline terminés.",
				open: "Ouvrir l'analytique de performance des moteurs →",
				back: "← Retour à la console runtime",
				loadFailed: "Impossible de charger l'analytique des moteurs.",
				empty:
					"Aucun historique d'exécution de moteur enregistré pour l'instant.",
				executions: "Exécutions",
				average: "Moyenne",
				fastest: "Plus rapide",
				slowest: "Plus lent",
				averageError: "Erreur d'estimation moy.",
				successRate: "Taux de réussite",
				relativeSpeed: "Vitesse relative",
			},
		},
		aiEngines: {
			title: "Moteurs IA",
			description:
				"Consultez les fournisseurs et capacités enregistrés (Sprint 34).",
		},
		pipeline: {
			title: "Configuration du pipeline",
			description:
				"Assignez des moteurs à chaque étape de traitement (Sprint 39).",
		},
		learning: {
			title: "Apprentissage adaptatif",
			description:
				"Consultez et contrôlez le profil d'apprentissage déterministe de Lumen.",
		},
		shadow: {
			title: "Identité Shadow",
			description:
				"Persona, studio vocal, compositeur linguistique et commandes vocales.",
		},
	},
	workItem: {
		types: {
			video: "Vidéo",
			pdf: "PDF",
			audio: "Audio",
			youtube: "YouTube",
			project: "Projet",
		},
		statuses: {
			processing: "En cours",
			completed: "Terminé",
			pending: "En attente",
			failed: "Échoué",
			ready: "Prêt",
		},
	},
	help: {
		explain: {
			defaultLabel: "Expliquer",
			dialogAria: "Explication de la fonctionnalité",
		},
		academy: {
			readingTime: "Lecture estimée : {{minutes}} min",
			sections: {
				whatIsIt: "Qu'est-ce que c'est ?",
				details: "Détails",
				bestPractice: "Bonne pratique",
				commonMistake: "Erreur courante",
				nextStep: "Étape suivante",
				faq: "FAQ",
			},
		},
		tooltip: {
			defaultLabel: "?",
			ariaLabel: "Aide : {{title}}",
		},
	},
};
