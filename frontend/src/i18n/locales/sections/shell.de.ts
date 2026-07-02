import type { DeepStringRecord } from "../../localeUtils";
import type { shellEn } from "./shell.en";

type ShellMessages = DeepStringRecord<typeof shellEn>;

export const shellDe: ShellMessages = {
	shell: {
		brand: {
			title: "Lumen",
			subtitle: "KI-Plattform für Video- und Wissenslokalisierung.",
			tagline: "Geführte Videolokalisierung",
		},
		sidebar: {
			ariaLabel: "Produktnavigation",
			shortcut: "Drücken Sie {{modifier}}+K, um überall zu suchen",
		},
		breadcrumbs: {
			ariaLabel: "Brotkrumen",
			home: "Start",
			import: "Import",
			video: "Video",
			upload: "Hochladen",
			transcript: "Transkript",
			translations: "Übersetzungen",
			audio: "Audio",
			youtube: "YouTube",
			"voice-clone": "Geklonte Stimme",
			"lip-sync": "Lip-Sync-Vorschau",
			render: "Finales Video",
			watch: "Shadow",
			workspace: "Arbeitsbereich",
			library: "Bibliothek",
			collections: "Sammlungen",
			settings: "Einstellungen",
			ai: "KI-Modelle",
			pipeline: "Pipeline-Einrichtung",
			processing: "Verarbeitung",
			overview: "Übersicht",
			videoId: "Video {{id}}…",
		},
		pageIntro: {
			whatCanIDo: "Was kann ich hier tun?",
		},
		nav: {
			groups: {
				create: "Erstellen",
				pipeline: "KI-Pipeline",
				results: "Ergebnisse",
				library: "Bibliothek",
				settings: "Einstellungen",
			},
			items: {
				dashboard: {
					label: "Start",
					description: "Orientierung, Erstellen und letzte Arbeiten.",
				},
				upload: {
					label: "Video hochladen",
					description: "Video hochladen und KI-Pipeline starten.",
				},
				import: {
					label: "Dokumente importieren",
					description: "PDF oder Audio für Wissensverarbeitung importieren.",
				},
				workspace: {
					label: "Arbeitsbereich",
					description: "Projekte, Stapelverarbeitung, Team und Analytik.",
				},
				"pipeline-settings": {
					label: "Pipeline-Einrichtung",
					description: "KI-Engines pro Verarbeitungsschritt wählen.",
				},
				"ai-engines": {
					label: "KI-Modelle",
					description: "Verfügbare KI-Anbieter und Fähigkeiten anzeigen.",
				},
				transcript: { label: "Transkript" },
				translations: { label: "Übersetzungen" },
				audio: { label: "Audio" },
				"voice-clone": { label: "Geklonte Stimme" },
				"lip-sync": { label: "Lip-Sync-Vorschau" },
				render: { label: "Finales Video" },
				shadow: { label: "Shadow" },
				library: { label: "Bibliothek" },
				collections: { label: "Sammlungen" },
				"settings-hub": {
					label: "Einstellungen",
					description: "App- und KI-Pipeline-Konfiguration.",
				},
			},
			empty: {
				transcript: {
					reason: "Noch kein Transkript.",
					why: "Transkripte ermöglichen Übersetzung, Audio und Wissenssuche.",
					action: "Video hochladen",
				},
				translations: {
					reason: "Noch keine Übersetzungen.",
					why: "Übersetzungen lokalisieren Inhalte für jede Zielsprache.",
					action: "Video öffnen",
				},
				audio: {
					reason: "Noch kein Audio generiert.",
					why: "Generierte Sprache treibt Voice-Clone und Lip-Sync an.",
					action: "Video öffnen",
				},
				"voice-clone": {
					reason: "Noch keine geklonte Stimme.",
					why: "Geklonte Stimme bewahrt die Identität des Originalsprechers.",
					action: "Zuerst Audio generieren",
				},
				"lip-sync": {
					reason: "Noch keine Lip-Sync-Vorschau.",
					why: "Lip-Sync richtet Lippenbewegungen vor dem Final-Render aus.",
					action: "Vorherige Schritte abschließen",
				},
				render: {
					reason: "Noch kein finales Video.",
					why: "Der Final-Render erzeugt eine herunterladbare lokalisierte MP4.",
					action: "Pipeline ausführen",
				},
				shadow: {
					reason: "Shadow-Watch noch nicht gestartet.",
					why: "Shadow hilft beim Lernen mit kontextbezogenen Fragen.",
					action: "Video öffnen",
				},
			},
		},
	},
	home: {
		eyebrow: "Start",
		title: "Lumen",
		description: "Wissen in Verständnis verwandeln.",
		whatCanIDo:
			"Video hochladen, Dokument importieren oder laufende Arbeit fortsetzen.",
		loading: "Start wird geladen",
		loadError:
			"Letzte Arbeiten konnten nicht geladen werden. Prüfen Sie, ob das Backend läuft.",
		errorTitle: "Start konnte nicht geladen werden",
		create: {
			heading: "Was möchten Sie transformieren?",
			nextPrefix: "Weiter:",
			ariaLabel: "{{type}} erstellen",
			video: {
				label: "Video",
				description:
					"Video hochladen und mit KI-Stimme, Lip-Sync und Render übersetzen.",
				nextStep: "Sprachen und KI-Modus nach dem Upload wählen.",
			},
			pdf: {
				label: "PDF",
				description: "Dokumente für Wissensextraktion und Chat importieren.",
				nextStep:
					"Verarbeitung erstellt Zusammenfassungen, Graph und Bibliothekseinträge.",
			},
			audio: {
				label: "Audio",
				description: "Audio importieren für Transkripte und Erkenntnisse.",
				nextStep: "Transkript steht zur Überprüfung bereit.",
			},
			youtube: {
				label: "YouTube",
				description: "YouTube-Quelle zur Verarbeitung verknüpfen.",
				nextStep: "Führt die vollständige Videolokalisierungs-Pipeline aus.",
			},
		},
		continue: {
			heading: "Arbeit fortsetzen",
			currentStep: "Aktueller Schritt:",
		},
		recent: {
			heading: "Letzte Arbeiten",
			empty:
				"Noch keine Arbeiten. Laden Sie ein Video hoch oder importieren Sie ein Dokument.",
			open: "Öffnen →",
			openAria: "{{title}} öffnen",
		},
		stats: {
			heading: "Auf einen Blick",
			videos: {
				label: "Videos",
				description: "Aktive Videoarbeiten",
				action: "Arbeitsbereich anzeigen →",
			},
			projects: {
				label: "Projekte",
				description: "Stapel- und Teamprojekte",
				action: "Projekte öffnen →",
			},
			completed: {
				label: "Abgeschlossen",
				description: "Fertige Arbeitselemente",
				action: "Bibliothek öffnen →",
			},
			artifacts: {
				label: "Artefakte",
				description: "Generierte Ausgaben",
				action: "Artefakte durchsuchen →",
			},
			ariaLabel: "{{label}}: {{count}}. {{action}}",
		},
		aiDirector: {
			heading: "KI-Direktor",
			loading: "Empfehlung wird geladen",
			recommended: "Empfohlener Workflow:",
			configureLink: "Beim Upload konfigurieren →",
			empty:
				"Video im automatischen Modus hochladen, um KI-Pipeline-Empfehlungen zu sehen.",
			uploadLink: "Video hochladen →",
		},
	},
	guidance: {
		palette: {
			closeAria: "Befehlspalette schließen",
			dialogAria: "Befehlspalette",
			placeholder: "Videos, Projekte, Pipeline, Analytik suchen…",
			empty: "Keine passenden Befehle.",
			footer: "{{count}} Befehle · Esc zum Schließen",
			groups: {
				Navigate: "Navigation",
				Create: "Erstellen",
				Results: "Ergebnisse",
				Settings: "Einstellungen",
				Library: "Bibliothek",
				Review: "Überblick",
			},
		},
		commands: {
			dashboard: {
				label: "Start",
				description: "Orientierung und letzte Arbeiten",
			},
			upload: {
				label: "Video hochladen",
				description: "Videolokalisierungs-Pipeline starten",
			},
			workspace: {
				label: "Arbeitsbereich",
				description: "Projekte, Stapel, Team, Analytik",
			},
			transcript: {
				label: "Transkript öffnen",
				description: "Erfordert Video im URL-Kontext",
			},
			translations: {
				label: "Übersetzungen öffnen",
				description: "Übersetzte Sprachen anzeigen",
			},
			audio: {
				label: "Audio öffnen",
				description: "Generiertes Audio anhören",
			},
			pipeline: {
				label: "Pipeline-Konfiguration",
				description: "KI-Engines pro Schritt konfigurieren",
			},
			ai: {
				label: "KI-Engines",
				description: "Verfügbare Anbieter anzeigen",
			},
			library: {
				label: "Bibliothek",
				description: "Gespeicherte Inhalte durchsuchen",
			},
			analytics: {
				label: "Arbeitsbereich-Analytik",
				description: "Telemetrie und Anbieterstatistiken",
			},
			import: {
				label: "Dokumente importieren",
				description: "PDF- und Audio-Import",
			},
		},
	},
	settings: {
		eyebrow: "Einstellungen",
		title: "Einstellungen",
		description: "Konfigurieren Sie, wie Lumen Ihre Videos verarbeitet.",
		whatCanIDo:
			"KI-Engines und Pipeline-Schritte wählen. Änderungen gelten für den nächsten Lauf.",
		language: {
			title: "Oberflächensprache",
			description: "Sprache für Menüs, Beschriftungen und Hilfetexte wählen.",
		},
		aiEngines: {
			title: "KI-Engines",
			description: "Registrierte Anbieter und Fähigkeiten (Sprint 34).",
		},
		pipeline: {
			title: "Pipeline-Konfiguration",
			description: "Engines pro Verarbeitungsschritt zuweisen (Sprint 39).",
		},
		learning: {
			title: "Adaptives Lernen",
			description: "Lumens deterministisches Lernprofil ansehen und steuern.",
		},
		shadow: {
			title: "Shadow-Identität",
			description: "Persona, Sprachstudio, Sprachkomponist und Sprachbefehle.",
		},
	},
	workItem: {
		types: {
			video: "Video",
			pdf: "PDF",
			audio: "Audio",
			youtube: "YouTube",
			project: "Projekt",
		},
		statuses: {
			processing: "In Bearbeitung",
			completed: "Abgeschlossen",
			pending: "Ausstehend",
			failed: "Fehlgeschlagen",
			ready: "Bereit",
		},
	},
	help: {
		explain: {
			defaultLabel: "Erklären",
			dialogAria: "Funktionserklärung",
		},
		academy: {
			readingTime: "Geschätzte Lesezeit: {{minutes}} Min.",
			sections: {
				whatIsIt: "Was ist das?",
				details: "Details",
				bestPractice: "Best Practice",
				commonMistake: "Häufiger Fehler",
				nextStep: "Nächster Schritt",
				faq: "FAQ",
			},
		},
		tooltip: {
			defaultLabel: "?",
			ariaLabel: "Hilfe: {{title}}",
		},
	},
};
