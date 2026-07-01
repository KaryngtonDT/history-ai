import type { DeepStringRecord } from "../../localeUtils";
import type { workspaceEn } from "./workspace.en";

type WorkspaceMessages = DeepStringRecord<typeof workspaceEn>;

export const workspaceDe: WorkspaceMessages = {
	workspace: {
		page: {
			loadingWorkspace: "Arbeitsbereich wird geladen",
			unableToLoadWorkspace: "Arbeitsbereich konnte nicht geladen werden",
			backendUnavailable:
				"Server konnte nicht erreicht werden. Prüfen Sie, ob das Backend läuft.",
			eyebrow: "Arbeitsbereich",
			title: "Projekt-Arbeitsbereich",
			description:
				"Organisieren Sie Videos, starten Sie Stapelverarbeitung und prüfen Sie Team-Ergebnisse.",
			whatCanIDo:
				"Erstellen Sie Projekte, fügen Sie Videos hinzu, wählen Sie Sprachen, starten Sie Stapelverarbeitung und prüfen Sie Analysen, Verlauf und Teamaktivität.",
			newProjectNamePlaceholder: "Neuer Projektname",
			newProjectNameAria: "Neuer Projektname",
			createProject: "Projekt erstellen",
			projects: "Projekte",
			noProjectsYet: "Noch keine Projekte.",
			noProjectsTitle: "Noch keine Projekte",
			noProjectsDescription:
				"Erstellen Sie ein Projekt, um Videos, Sprachen und Stapelverarbeitung zu organisieren.",
			noProjectsAction: "Erstes Projekt erstellen",
			providerStatistics: "Anbieterstatistiken",
			performance: "Leistung",
			qualityTrend: "Qualitätstrend",
			videos: "Videos",
			languages: "Sprachen",
			reviewHistory: "Review-Verlauf",
			selectedVideoPipeline: "Pipeline des ausgewählten Videos",
			selectProjectTitle: "Projekt auswählen",
			selectProjectDescription:
				"Erstellen oder wählen Sie ein Projekt, um Videos und Stapelverarbeitung zu verwalten.",
			tabsAria: "Workspace-Bereiche",
			tabs: {
				projects: "Projekte",
				team: "Team",
				analytics: "Analytik",
				history: "Verlauf",
				reviews: "Reviews",
				preferences: "Einstellungen",
			},
			stickyVideosOne: "{{count}} Video ausgewählt",
			stickyVideosOther: "{{count}} Videos ausgewählt",
			stickyLanguages: "Zielsprachen: {{languages}}",
			stickyLanguagesEmpty: "Keine Zielsprachen ausgewählt",
		},
		projectCard: {
			videoCountOne: "{{count}} Video",
			videoCountOther: "{{count}} Videos",
		},
		videoGrid: {
			emptyTitle: "Keine Videos in diesem Projekt",
			emptyDescription:
				"Fügen Sie Videos hinzu, um Stapel-Lokalisierung für Zielsprachen zu starten.",
			emptyAction: "Video hochladen",
			empty:
				"Fügen Sie diesem Projekt Videos hinzu, um die Stapelverarbeitung zu starten.",
			openPipeline: "Pipeline öffnen →",
			remove: "Entfernen",
			removeAria: "{{filename}} entfernen",
		},
		batch: {
			starting: "Stapelverarbeitung wird gestartet...",
			overallProgress: "Gesamtfortschritt",
			progressAria: "Stapel-Fortschritt",
			status: {
				idle: "Leerlauf",
				pending: "Ausstehend",
				running: "Läuft",
				completed: "Abgeschlossen",
				partial_failure: "Teilweise fehlgeschlagen",
				failed: "Fehlgeschlagen",
			},
			processButtonOne: "{{count}} Video verarbeiten",
			processButtonOther: "{{count}} Videos verarbeiten",
		},
		team: {
			eyebrow: "Team",
			title: "Mitglieder",
			noMembersYet: "Noch keine Mitglieder.",
			pendingInvitations: "Ausstehende Einladungen",
			inviteMember: "Mitglied einladen",
			emailAddressPlaceholder: "E-Mail-Adresse",
			memberEmailAria: "E-Mail des Mitglieds",
			sendInvitation: "Einladung senden",
			remove: "Entfernen",
			roleLabel: "Rolle",
			errors: {
				loadMembers: "Teammitglieder konnten nicht geladen werden.",
				sendInvitation: "Einladung konnte nicht gesendet werden.",
				updateMemberRole: "Mitgliederrolle konnte nicht aktualisiert werden.",
				removeMember: "Mitglied konnte nicht entfernt werden.",
			},
			roles: {
				owner: "Besitzer",
				editor: "Editor",
				reviewer: "Prüfer",
				viewer: "Betrachter",
			},
		},
		history: {
			versionHistory: "Versionsverlauf",
			loadingExecutionHistory: "Ausführungsverlauf wird geladen...",
			compare: "Vergleichen",
			reprocess: "Neu verarbeiten",
			noExecutionHistory: "Noch kein Ausführungsverlauf erfasst.",
			emptyTitle: "Kein Ausführungsverlauf",
			emptyDescription:
				"Der Verlauf erscheint nach der Verarbeitung eines Videos in diesem Workspace.",
			emptyAction: "Video öffnen",
			noVideoTitle: "Kein Video ausgewählt",
			noVideoDescription:
				"Wählen Sie ein Projekt mit Videos, um Versionen zu vergleichen.",
			noVideoAction: "Zu Projekten",
			versionLabel: "V{{version}}",
			scoreLabel: "Punktzahl {{score}}",
			comparingVersions: "Versionen werden verglichen...",
			compareVersions: "V{{left}} mit V{{right}} vergleichen",
			providerDifferences: "Anbieterunterschiede",
			optimization: "Optimierung",
			qualityScore: "Qualitätspunktzahl",
		},
		review: {
			title: "Review",
			comment: "Kommentar",
			commentPlaceholder: "Die geklonte Stimme klingt etwas zu robotisch.",
			saveFeedback: "Feedback speichern",
			saving: "Speichern...",
			feedbackSaved: "Feedback gespeichert.",
			feedbackSaveFailed: "Feedback konnte nicht gespeichert werden.",
			starsAria: "{{category}} {{score}} Sterne",
			historyVersion: "Version {{version}}",
			noReviewsYet: "Noch keine Reviews.",
			emptyTitle: "Noch keine Reviews",
			emptyDescription:
				"Speichern Sie Feedback zu einer Pipeline-Version für künftige Läufe.",
			emptyAction: "Review abgeben",
			categoryLabels: {
				overall: "Gesamt",
				translation: "Übersetzung",
				voice_clone: "Stimme",
				lip_sync: "Lip-Sync",
				rendering: "Rendering",
			},
			preferenceProfile: {
				title: "Präferenzprofil",
				empty:
					"Senden Sie Reviews, um Ihr adaptives Präferenzprofil aufzubauen.",
				emptyTitle: "Noch kein Präferenzprofil",
				emptyDescription:
					"Reviews lehren Lumen Ihre Übersetzungs-, Stimm- und Render-Präferenzen.",
				emptyAction: "Review senden",
				translationStyle: "Übersetzungsstil",
				voiceStability: "Stimmstabilität",
				renderingPreset: "Rendering-Voreinstellung",
				lipSyncStrength: "Lip-Sync-Stärke",
			},
		},
		analytics: {
			title: "Arbeitsbereich-Analytik",
			loading: "Analytik wird geladen...",
			emptyTitle: "Noch keine Analytik",
			emptyDescription:
				"Verarbeiten Sie Videos in einem Projekt, um Durchsatz, Qualität und Anbieter zu sehen.",
			emptyAction: "Zu Projekten",
			noProviderStats: "Noch keine Anbieterstatistiken.",
			noProviderStatsTitle: "Keine Anbieterstatistiken",
			noProviderStatsDescription:
				"Starten Sie Stapelverarbeitung, um KI-Anbieter zu vergleichen.",
			noProviderStatsAction: "Videos verarbeiten",
			providerRuns: "{{count}} Läufe",
			averageDuration: "Ø {{seconds}}s",
			noPerformanceSamples: "Noch keine Leistungsdaten.",
			noPerformanceTitle: "Keine Leistungsdaten",
			noPerformanceDescription:
				"Diagramme erscheinen nach abgeschlossenen Pipeline-Läufen in diesem Workspace.",
			noPerformanceAction: "Videos verarbeiten",
			runLabel: "Lauf {{index}}",
			noQualityTrendData: "Noch keine Qualitätstrend-Daten.",
			noQualityTitle: "Kein Qualitätstrend",
			noQualityDescription:
				"Qualitätswerte werden erfasst, wenn Videos die Verarbeitung abschließen.",
			noQualityAction: "Videos verarbeiten",
			lastErrors: "Letzte Fehler",
			labels: {
				processedVideos: "Verarbeitete Videos",
				averageProcessingTime: "Durchschnittliche Verarbeitungszeit",
				averageQuality: "Durchschnittliche Qualität",
				successRate: "Erfolgsquote",
				gpuUsage: "GPU-Auslastung",
				topTranslationProvider: "Top-Übersetzungsanbieter",
				topTtsProvider: "Top-TTS-Anbieter",
			},
		},
		settings: {
			pipeline: {
				eyebrow: "Einstellungen",
				title: "Pipeline-Konfiguration",
				description: "Weisen Sie jeder Verarbeitungsstufe KI-Engines zu.",
				whatCanIDo:
					"Ordnen Sie Anbieter für Transkript, Übersetzung, Audio, Stimmenklon, Lip-Sync und Rendering zu. Änderungen gelten beim nächsten Lauf.",
			},
			aiEngine: {
				eyebrow: "Einstellungen",
				title: "KI-Engines",
				description:
					"Sehen Sie registrierte Anbieter und die Fähigkeiten jeder Engine.",
				whatCanIDo:
					"Prüfen Sie verfügbare Anbieter für Sprache, Übersetzung, TTS, Stimmenklon und Lip-Sync, bevor Sie Ihre Pipeline konfigurieren.",
			},
		},
		library: {
			headerTitle: "Bibliothek",
			headerDescription:
				"Ihre importierten Wissensquellen und Lernmaterialien.",
			searchLabel: "Bibliothek durchsuchen",
			searchPlaceholder: "Nach Titel suchen...",
			typeLabels: {
				summary: "Zusammenfassung",
				quiz: "Quiz",
				flashcards: "Karteikarten",
				transcript: "Transkript",
				timeline: "Zeitachse",
				podcast: "Podcast",
			},
			addToCollection: "Zur Sammlung hinzufügen",
			searchingLibrary: "Bibliothek wird durchsucht",
			unableToSearchLibrary: "Bibliothek konnte nicht durchsucht werden",
			noResultsFound: "Keine Ergebnisse gefunden",
			tryDifferentSearchTerm: "Versuchen Sie einen anderen Suchbegriff.",
			loadingLibrary: "Bibliothek wird geladen",
			unableToLoadLibrary: "Bibliothek konnte nicht geladen werden",
			noLibraryItemsYet: "Noch keine Bibliothekseinträge",
			noLibraryItemsDescription:
				"Gespeicherte Lernartefakte erscheinen hier, sobald Sie sie zur Bibliothek hinzufügen.",
			itemDetails: {
				loading: "Bibliothekseintrag wird geladen",
				notFoundTitle: "Bibliothekseintrag nicht gefunden",
				notFoundDescription:
					"Zur Bibliothek zurückkehren, um gespeicherte Einträge zu durchsuchen.",
				artifactNotFoundTitle: "Artefakt nicht gefunden",
				artifactNotFoundDescription:
					"Das verknüpfte Artefakt ist für diesen Bibliothekseintrag nicht mehr verfügbar.",
				unableToLoad: "Bibliothekseintrag konnte nicht geladen werden",
				backToLibrary: "Zurück zur Bibliothek",
			},
		},
		collections: {
			headerTitle: "Sammlungen",
			headerDescription:
				"Organisieren Sie Bibliothekseinträge in thematischen Gruppen.",
			createCollection: "Sammlung erstellen",
			itemCount: "Einträge: -",
			loadingCollections: "Sammlungen werden geladen",
			unableToLoadCollections: "Sammlungen konnten nicht geladen werden",
			noCollectionsYet: "Noch keine Sammlungen",
			noCollectionsDescription:
				"Erstellen Sie Ihre erste Sammlung, um Bibliothekseinträge zu organisieren.",
			createDialog: {
				title: "Sammlung erstellen",
				description:
					"Geben Sie Ihrer Sammlung einen Namen und optional eine Beschreibung.",
				name: "Name",
				descriptionLabel: "Beschreibung",
				creatingCollection: "Sammlung wird erstellt",
				creating: "Wird erstellt...",
				create: "Erstellen",
				error:
					"Die Sammlung konnte nicht erstellt werden. Bitte versuchen Sie es erneut.",
			},
			assignDialog: {
				title: "Zu Sammlung zuweisen",
				description: "Wählen Sie eine Sammlung für diesen Bibliothekseintrag.",
				loadingCollections: "Sammlungen werden geladen",
				collection: "Sammlung",
				assigningCollection: "Zuweisung zur Sammlung",
				assigning: "Wird zugewiesen...",
				assign: "Zuweisen",
				success: "Bibliothekseintrag erfolgreich zugewiesen.",
				done: "Fertig",
				duplicate:
					"Dieser Bibliothekseintrag befindet sich bereits in der ausgewählten Sammlung.",
				loadCollectionsError:
					"Sammlungen konnten nicht geladen werden. Bitte versuchen Sie es erneut.",
				assignError:
					"Bibliothekseintrag konnte nicht zugewiesen werden. Bitte versuchen Sie es erneut.",
			},
		},
		import: {
			couldNotStartProcessing:
				"Verarbeitung konnte nicht gestartet werden. Prüfen Sie, ob das Backend läuft, und versuchen Sie es erneut.",
			eyebrow: "Erstellen",
			headerTitle: "Wissen importieren",
			headerDescription:
				"Bringen Sie Wissensquellen in Lumen. Wählen Sie zum Start eine PDF-Datei aus.",
			whatCanIDo:
				"Laden Sie eine PDF hoch, um Inhalt zu extrahieren, einen Verarbeitungsjob zu erstellen und den geführten Zusammenfassungs-Workflow zu öffnen.",
			whatHappensNext:
				"Lumen importiert die PDF, erstellt einen Inhaltsdatensatz und startet einen Zusammenfassungsjob. Fortschritt auf der Verarbeitungsseite verfolgen.",
			supportedFiles:
				"Unterstützte Dateien: nur PDF. Textbasierte PDFs funktionieren am besten.",
			helpTitle: "Hilfe",
			pdfDropzoneAria: "PDF-Ablagebereich",
			dropPdfTitle: "PDF hier ablegen",
			dropPdfDescription: "Oder eine Datei von Ihrem Gerät auswählen.",
			selectPdf: "PDF auswählen",
			uploading: "Wird hochgeladen",
			processing: "Verarbeitung",
			uploadingFile: "Datei wird hochgeladen",
			uploadFailed: "Upload fehlgeschlagen",
			tryAgain: "Erneut versuchen",
		},
		processing: {
			loadingStatus: "Verarbeitungsstatus wird geladen",
			jobNotFound: "Verarbeitungsauftrag nicht gefunden",
			returnToDashboard: "Zurück zum Dashboard, um fortzufahren.",
			unableToLoad: "Verarbeitung konnte nicht geladen werden",
			heading: "Verarbeitung",
			progress: "Fortschritt",
			currentStep: "Aktueller Schritt",
			processingStep: "Verarbeitungsschritt",
			status: {
				completed: "Abgeschlossen",
				running: "Läuft",
				failed: "Fehlgeschlagen",
				cancelled: "Abgebrochen",
				pending: "Ausstehend",
			},
			complete: "Verarbeitung abgeschlossen",
			ready: "Bereit",
			completeMessage:
				"{{title}} wurde in Lernartefakte umgewandelt. Sie erscheinen in Kürze in Ihrer Bibliothek.",
			pipeline: "Pipeline",
			loadingArtifacts: "Artefakte werden geladen",
			unableToLoadArtifacts: "Artefakte konnten nicht geladen werden",
			noArtifactsYet: "Noch keine Artefakte",
			noArtifactsDescription:
				"Generierte Lernartefakte erscheinen hier, sobald Verarbeitungsausgaben verfügbar sind.",
		},
	},
};
