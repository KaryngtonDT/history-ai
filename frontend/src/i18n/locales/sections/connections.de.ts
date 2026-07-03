export const connectionsDe = {
	connections: {
		title: "Verbindungen",
		description:
			"Wählen Sie, wie Shadow-Clients Ihren Lumen-Heimserver erreichen — localhost, LAN, Tailscale oder automatisch.",
		whatCanIDo:
			"Verbindungsmodus und Endpunkte für privaten Remote-Zugriff ohne öffentliche Ports setzen.",
		mode: {
			title: "Verbindungsprofil",
			localhost: "Localhost",
			lan: "LAN",
			auto: "Auto (empfohlen)",
			tailscale: "Tailscale",
			cloud: "Cloud (zukünftig)",
		},
		endpoints: {
			title: "Endpunkte",
			localhost: "Localhost-URL",
			lan: "LAN-URL",
			tailscale: "Tailscale-URL",
			homeWifi: "Heim-WLAN-SSIDs (kommagetrennt)",
		},
		actions: {
			save: "Profil speichern",
		},
		errors: {
			loadFailed: "Verbindungseinstellungen konnten nicht geladen werden.",
			saveFailed: "Verbindungseinstellungen konnten nicht gespeichert werden.",
		},
	},
};
