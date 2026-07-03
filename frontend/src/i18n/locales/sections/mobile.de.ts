export const mobileDe = {
	mobile: {
		title: "Shadow Mobile",
		description:
			"Shadow-Mobile-Begleiter konfigurieren — Verbindungsprofil, Today-Dashboard und Sync mit dem Heimserver.",
		whatCanIDo:
			"Gerät registrieren, heutige Missionen und Wiederholungen prüfen und Remote-Zugriff über Tailscale verwalten.",
		tabs: {
			mobile: "Mobile",
		},
		status: {
			title: "Mobile Verbindung",
			connected: "Shadow verbunden",
			disconnected: "Nicht verbunden",
			mode: "Modus: {{mode}}",
			server: "Server: {{status}}",
			device: "Gerät: {{name}} ({{platform}})",
			noDevice: "Noch kein Mobilgerät registriert.",
		},
		today: {
			title: "Heute",
			noMissions: "Keine Missionen geplant.",
			noRevisions: "Keine Wiederholungen fällig.",
		},
		actions: {
			registerDemo: "Demo-Gerät registrieren",
			sync: "Jetzt synchronisieren",
		},
		errors: {
			loadFailed: "Mobile-Einstellungen konnten nicht geladen werden.",
			registerFailed: "Gerät konnte nicht registriert werden.",
			syncFailed: "Synchronisation fehlgeschlagen.",
		},
	},
};
