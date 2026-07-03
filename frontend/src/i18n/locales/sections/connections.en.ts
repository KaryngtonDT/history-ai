export const connectionsEn = {
	connections: {
		title: "Connections",
		description:
			"Choose how Shadow clients reach your home Lumen server — localhost, LAN, Tailscale, or automatic switching.",
		whatCanIDo:
			"Set connection mode and endpoints for Personal Remote access without opening public ports.",
		mode: {
			title: "Connection profile",
			localhost: "Localhost",
			lan: "LAN",
			auto: "Auto (recommended)",
			tailscale: "Tailscale",
			cloud: "Cloud (future)",
		},
		endpoints: {
			title: "Endpoints",
			localhost: "Localhost URL",
			lan: "LAN URL",
			tailscale: "Tailscale URL",
			homeWifi: "Home Wi‑Fi SSIDs (comma-separated)",
		},
		actions: {
			save: "Save connection profile",
		},
		errors: {
			loadFailed: "Could not load connection settings.",
			saveFailed: "Could not save connection settings.",
		},
	},
};
