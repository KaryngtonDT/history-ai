export const browserDe = {
	browser: {
		title: "Shadow-Browser",
		description:
			"Konfigurieren Sie den Shadow-Browser-Begleiter — Plattformerkennung, Website-Berechtigungen, Lesekontext und Erklärbarkeit.",
		whatCanIDo:
			"Verbinden Sie die Browser-Sitzung, prüfen Sie Plattformen, verwalten Sie Berechtigungen pro Website, sehen Sie Aktivitäten ein und testen Sie den Lesekontext.",
		tabs: {
			browser: "Browser",
		},
		status: {
			title: "Browser-Sitzung",
			active: "Verbunden ({{state}})",
			inactive: "Nicht verbunden",
			connect: "Browser verbinden",
			disconnect: "Trennen",
			lastActive: "Zuletzt aktiv: {{time}}",
		},
		platform: {
			title: "Plattform-Inspektor",
			urlPlaceholder: "https://www.youtube.com/watch?v=...",
			detect: "Plattform erkennen",
			result: "{{platform}} erkannt auf {{host}}",
		},
		permissions: {
			title: "Berechtigungszentrum",
			siteAllowed: "Website erlaubt",
			save: "Website-Berechtigungen speichern",
			noYoutube: "Noch keine youtube.com-Richtlinie — speichern zum Anlegen.",
			allowed: "Erlaubt",
			blocked: "Blockiert",
		},
		history: {
			title: "Aktivitätsverlauf",
			empty: "Noch keine Browser-Aktivität.",
		},
		explain: {
			title: "Warum hat Shadow gehandelt?",
			reason: "Grund: {{reason}}",
			detail: "{{detail}}",
			refresh: "Erklärung aktualisieren",
		},
		reading: {
			title: "Lesebegleiter-Demo",
			description:
				"Simulieren Sie Textauswahl auf einer Leseseite. Shadow nutzt diesen Kontext für Fragen, Suche und Fortsetzen.",
			url: "Seiten-URL",
			pageTitle: "Seitentitel",
			selection: "Ausgewählter Text",
			send: "Kontext senden",
			contextPreview:
				"Kontext aktualisiert — Plattform: {{platform}}, Auswahl: „{{selection}}“",
		},
		youtube: {
			title: "YouTube-Begleiter",
			description:
				"Für Video-Lernen öffnen Sie den Shadow-Watch-Modus — der Begleiter nutzt Transkript und Timeline statt Seitenauswahl.",
			openWatch: "Shadow-Watch-Modus öffnen",
		},
		extension: {
			title: "Browser-Erweiterung",
			description:
				"Installieren Sie die Shadow-Browser-Erweiterung, um echte Tabs zu verbinden, Auswahl zu erfassen und Kontext automatisch zu synchronisieren.",
			comingSoon: "Erweiterungs-Paketierung in einem zukünftigen Sprint",
		},
		errors: {
			loadFailed: "Browser-Einstellungen konnten nicht geladen werden.",
		},
	},
};
