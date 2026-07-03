export const serverDe = {
	server: {
		title: "Persönlicher Server",
		description:
			"Überwachen Sie Ihren Lumen-Heimserver — Docker-Gesundheit, Readiness und Tailscale-Status.",
		whatCanIDo:
			"Prüfen Sie die Erreichbarkeit, bevor Sie Shadow Mobile unterwegs nutzen.",
		overview: {
			title: "Serverübersicht",
			available: "Verfügbar: {{value}}",
			checks: "Checks: {{healthy}}/{{total}} OK",
			mode: "Verbindungsmodus: {{mode}}",
			status: "Status: {{status}} · Live: {{live}}",
		},
		checks: {
			title: "Gesundheitsprüfungen",
			empty: "Keine Prüfungen gemeldet.",
		},
		errors: {
			loadFailed: "Server-Dashboard konnte nicht geladen werden.",
		},
	},
};
