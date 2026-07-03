export const serverFr = {
	server: {
		title: "Serveur personnel",
		description:
			"Surveillez votre serveur Lumen domestique — santé Docker, readiness et connexion Tailscale.",
		whatCanIDo:
			"Vérifiez que le serveur est joignable avant d'utiliser Shadow Mobile à distance.",
		overview: {
			title: "Vue d'ensemble",
			available: "Disponible : {{value}}",
			checks: "Contrôles : {{healthy}}/{{total}} OK",
			mode: "Mode : {{mode}}",
			status: "Statut : {{status}} · Live : {{live}}",
		},
		checks: {
			title: "Contrôles de santé",
			empty: "Aucun contrôle signalé.",
		},
		errors: {
			loadFailed: "Impossible de charger le tableau de bord serveur.",
		},
	},
};
