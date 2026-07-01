import type { DeepStringRecord } from "../../localeUtils";
import type { workspaceEn } from "./workspace.en";

type WorkspaceMessages = DeepStringRecord<typeof workspaceEn>;

export const workspaceFr: WorkspaceMessages = {
	workspace: {
		page: {
			loadingWorkspace: "Chargement de l'espace de travail",
			unableToLoadWorkspace: "Impossible de charger l'espace de travail",
			backendUnavailable:
				"Impossible de joindre le serveur. Vérifiez que le backend est démarré.",
			eyebrow: "Espace de travail",
			title: "Espace de projets",
			description:
				"Organisez les vidéos, lancez le traitement par lot et suivez le travail de l'équipe.",
			whatCanIDo:
				"Créez des projets, ajoutez des vidéos, choisissez des langues, lancez des lots et consultez les analyses, l'historique et l'activité de l'équipe.",
			newProjectNamePlaceholder: "Nom du nouveau projet",
			newProjectNameAria: "Nom du nouveau projet",
			createProject: "Créer un projet",
			projects: "Projets",
			noProjectsYet: "Aucun projet pour le moment.",
			providerStatistics: "Statistiques des fournisseurs",
			performance: "Performance",
			qualityTrend: "Tendance qualité",
			videos: "Vidéos",
			languages: "Langues",
			reviewHistory: "Historique des avis",
			selectedVideoPipeline: "Pipeline de la vidéo sélectionnée",
			selectProjectTitle: "Sélectionnez un projet",
			selectProjectDescription:
				"Créez ou choisissez un projet pour gérer les vidéos et le traitement par lot.",
		},
		projectCard: {
			videoCountOne: "{{count}} vidéo",
			videoCountOther: "{{count}} vidéos",
		},
		videoGrid: {
			empty:
				"Ajoutez des vidéos à ce projet pour démarrer le traitement par lot.",
			openPipeline: "Ouvrir le pipeline →",
			remove: "Supprimer",
			removeAria: "Supprimer {{filename}}",
		},
		batch: {
			starting: "Démarrage du traitement par lot...",
			overallProgress: "Progression globale",
			progressAria: "Progression du lot",
			status: {
				idle: "Inactif",
				pending: "En attente",
				running: "En cours",
				completed: "Terminé",
				partial_failure: "Échec partiel",
				failed: "Échec",
			},
			processButtonOne: "Traiter {{count}} vidéo",
			processButtonOther: "Traiter {{count}} vidéos",
		},
		team: {
			eyebrow: "Équipe",
			title: "Membres",
			noMembersYet: "Aucun membre pour le moment.",
			pendingInvitations: "Invitations en attente",
			inviteMember: "Inviter un membre",
			emailAddressPlaceholder: "Adresse e-mail",
			memberEmailAria: "E-mail du membre",
			sendInvitation: "Envoyer l'invitation",
			remove: "Supprimer",
			roleLabel: "Rôle",
			errors: {
				loadMembers: "Impossible de charger les membres de l'équipe.",
				sendInvitation: "Impossible d'envoyer l'invitation.",
				updateMemberRole: "Impossible de mettre à jour le rôle du membre.",
				removeMember: "Impossible de supprimer le membre.",
			},
			roles: {
				owner: "Propriétaire",
				editor: "Éditeur",
				reviewer: "Réviseur",
				viewer: "Lecteur",
			},
		},
		history: {
			versionHistory: "Historique des versions",
			loadingExecutionHistory: "Chargement de l'historique d'exécution...",
			compare: "Comparer",
			reprocess: "Retraiter",
			noExecutionHistory: "Aucun historique d'exécution enregistré.",
			versionLabel: "V{{version}}",
			scoreLabel: "Score {{score}}",
			comparingVersions: "Comparaison des versions...",
			compareVersions: "Comparer V{{left}} vs V{{right}}",
			providerDifferences: "Différences de fournisseur",
			optimization: "Optimisation",
			qualityScore: "Score qualité",
		},
		review: {
			title: "Avis",
			comment: "Commentaire",
			commentPlaceholder: "La voix clonée est légèrement trop robotique.",
			saveFeedback: "Enregistrer l'avis",
			saving: "Enregistrement...",
			feedbackSaved: "Avis enregistré.",
			feedbackSaveFailed: "Impossible d'enregistrer l'avis.",
			starsAria: "{{category}} {{score}} étoiles",
			historyVersion: "Version {{version}}",
			noReviewsYet: "Aucun avis pour le moment.",
			categoryLabels: {
				overall: "Global",
				translation: "Traduction",
				voice_clone: "Voix",
				lip_sync: "Lip sync",
				rendering: "Rendu",
			},
			preferenceProfile: {
				title: "Profil de préférences",
				empty:
					"Envoyez des avis pour construire votre profil de préférences adaptatif.",
				translationStyle: "Style de traduction",
				voiceStability: "Stabilité de la voix",
				renderingPreset: "Préréglage de rendu",
				lipSyncStrength: "Intensité du lip sync",
			},
		},
		analytics: {
			title: "Analyse de l'espace de travail",
			loading: "Chargement de l'analyse...",
			noProviderStats: "Aucune statistique fournisseur pour le moment.",
			providerRuns: "{{count}} exécutions",
			averageDuration: "moy {{seconds}}s",
			noPerformanceSamples: "Aucun échantillon de performance.",
			runLabel: "Exécution {{index}}",
			noQualityTrendData: "Aucune donnée de tendance qualité.",
			lastErrors: "Dernières erreurs",
			labels: {
				processedVideos: "Vidéos traitées",
				averageProcessingTime: "Temps moyen de traitement",
				averageQuality: "Qualité moyenne",
				successRate: "Taux de réussite",
				gpuUsage: "Utilisation GPU",
				topTranslationProvider: "Principal fournisseur de traduction",
				topTtsProvider: "Principal fournisseur TTS",
			},
		},
		settings: {
			pipeline: {
				eyebrow: "Paramètres",
				title: "Configuration du pipeline",
				description: "Attribuez des moteurs IA à chaque étape de traitement.",
				whatCanIDo:
					"Associez des fournisseurs aux étapes transcription, traduction, audio, clonage de voix, lip sync et rendu. Les changements s'appliquent au prochain traitement.",
			},
			aiEngine: {
				eyebrow: "Paramètres",
				title: "Moteurs IA",
				description:
					"Consultez les fournisseurs enregistrés et les capacités de chaque moteur.",
				whatCanIDo:
					"Vérifiez les fournisseurs disponibles pour parole, traduction, TTS, clonage de voix et lip sync avant de configurer votre pipeline.",
			},
		},
		library: {
			headerTitle: "Bibliothèque",
			headerDescription:
				"Vos sources de connaissance importées et vos supports d'apprentissage.",
			searchLabel: "Rechercher dans la bibliothèque",
			searchPlaceholder: "Rechercher par titre...",
			typeLabels: {
				summary: "Résumé",
				quiz: "Quiz",
				flashcards: "Cartes mémoire",
				transcript: "Transcription",
				timeline: "Chronologie",
				podcast: "Podcast",
			},
			addToCollection: "Ajouter à une collection",
			searchingLibrary: "Recherche dans la bibliothèque",
			unableToSearchLibrary:
				"Impossible d'effectuer la recherche dans la bibliothèque",
			noResultsFound: "Aucun résultat",
			tryDifferentSearchTerm: "Essayez un autre terme de recherche.",
			loadingLibrary: "Chargement de la bibliothèque",
			unableToLoadLibrary: "Impossible de charger la bibliothèque",
			noLibraryItemsYet: "Aucun élément de bibliothèque pour le moment",
			noLibraryItemsDescription:
				"Les artefacts d'apprentissage enregistrés apparaîtront ici une fois ajoutés à votre bibliothèque.",
			itemDetails: {
				loading: "Chargement de l'élément de bibliothèque",
				notFoundTitle: "Élément de bibliothèque introuvable",
				notFoundDescription:
					"Revenez à la bibliothèque pour parcourir les éléments enregistrés.",
				artifactNotFoundTitle: "Artefact introuvable",
				artifactNotFoundDescription:
					"L'artefact lié n'est plus disponible pour cet élément de bibliothèque.",
				unableToLoad: "Impossible de charger l'élément de bibliothèque",
				backToLibrary: "Retour à la bibliothèque",
			},
		},
		collections: {
			headerTitle: "Collections",
			headerDescription:
				"Organisez les éléments de bibliothèque en groupes thématiques.",
			createCollection: "Créer une collection",
			itemCount: "Éléments : -",
			loadingCollections: "Chargement des collections",
			unableToLoadCollections: "Impossible de charger les collections",
			noCollectionsYet: "Aucune collection pour le moment",
			noCollectionsDescription:
				"Créez votre première collection pour organiser vos éléments de bibliothèque.",
			createDialog: {
				title: "Créer une collection",
				description:
					"Donnez un nom à votre collection et ajoutez une description facultative.",
				name: "Nom",
				descriptionLabel: "Description",
				creatingCollection: "Création de la collection",
				creating: "Création...",
				create: "Créer",
				error: "Impossible de créer la collection. Veuillez réessayer.",
			},
			assignDialog: {
				title: "Assigner à une collection",
				description: "Choisissez une collection pour cet élément.",
				loadingCollections: "Chargement des collections",
				collection: "Collection",
				assigningCollection: "Assignation à la collection",
				assigning: "Assignation...",
				assign: "Assigner",
				success: "Élément de bibliothèque assigné avec succès.",
				done: "Terminé",
				duplicate:
					"Cet élément de bibliothèque est déjà dans la collection sélectionnée.",
				loadCollectionsError:
					"Impossible de charger les collections. Veuillez réessayer.",
				assignError:
					"Impossible d'assigner l'élément de bibliothèque. Veuillez réessayer.",
			},
		},
		import: {
			couldNotStartProcessing:
				"Impossible de démarrer le traitement. Vérifiez que le backend est démarré puis réessayez.",
			headerTitle: "Importer",
			headerDescription:
				"Importez des sources de connaissance dans History AI. Sélectionnez un PDF pour commencer.",
			pdfDropzoneAria: "Zone de dépôt PDF",
			dropPdfTitle: "Déposez votre PDF ici",
			dropPdfDescription: "Ou sélectionnez un fichier depuis votre appareil.",
			selectPdf: "Sélectionner un PDF",
			uploading: "Téléversement",
			processing: "Traitement",
			uploadingFile: "Téléversement du fichier",
			uploadFailed: "Échec du téléversement",
			tryAgain: "Réessayer",
		},
		processing: {
			loadingStatus: "Chargement de l'état du traitement",
			jobNotFound: "Tâche de traitement introuvable",
			returnToDashboard: "Revenez au tableau de bord pour continuer.",
			unableToLoad: "Impossible de charger le traitement",
			heading: "Traitement",
			progress: "Progression",
			currentStep: "Étape actuelle",
			processingStep: "Étape de traitement",
			status: {
				completed: "Terminé",
				running: "En cours",
				failed: "Échec",
				cancelled: "Annulé",
				pending: "En attente",
			},
			complete: "Traitement terminé",
			ready: "Prêt",
			completeMessage:
				"{{title}} a été transformé en artefacts d'apprentissage. Ils apparaîtront bientôt dans votre bibliothèque.",
			pipeline: "Pipeline",
			loadingArtifacts: "Chargement des artefacts",
			unableToLoadArtifacts: "Impossible de charger les artefacts",
			noArtifactsYet: "Aucun artefact pour le moment",
			noArtifactsDescription:
				"Les artefacts d'apprentissage générés apparaîtront ici une fois la sortie de traitement disponible.",
		},
	},
};
