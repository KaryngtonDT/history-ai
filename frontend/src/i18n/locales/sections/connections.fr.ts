export const connectionsFr = {
	connections: {
		title: "Connexions",
		description:
			"Choisissez comment les clients Shadow atteignent Lumen — localhost, LAN, Tailscale ou bascule automatique.",
		whatCanIDo:
			"Définissez le mode et les URLs pour l'accès distant privé sans ouvrir de ports publics.",
		mode: {
			title: "Profil de connexion",
			localhost: "Localhost",
			lan: "LAN",
			auto: "Auto (recommandé)",
			tailscale: "Tailscale",
			cloud: "Cloud (futur)",
		},
		endpoints: {
			title: "Points de terminaison",
			localhost: "URL localhost",
			lan: "URL LAN",
			tailscale: "URL Tailscale",
			homeWifi: "SSID Wi‑Fi maison (séparés par des virgules)",
		},
		actions: {
			save: "Enregistrer le profil",
		},
		errors: {
			loadFailed: "Impossible de charger les connexions.",
			saveFailed: "Impossible d'enregistrer les connexions.",
		},
	},
};
