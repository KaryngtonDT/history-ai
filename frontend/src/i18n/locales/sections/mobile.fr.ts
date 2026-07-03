export const mobileFr = {
	mobile: {
		title: "Shadow Mobile",
		description:
			"Configurez le compagnon mobile Shadow — profil de connexion, tableau Today et synchronisation avec votre serveur domestique.",
		whatCanIDo:
			"Enregistrez un appareil, consultez les missions et révisions du jour, et gérez l'accès distant via Tailscale.",
		tabs: {
			mobile: "Mobile",
		},
		status: {
			title: "Connexion mobile",
			connected: "Shadow connecté",
			disconnected: "Non connecté",
			mode: "Mode : {{mode}}",
			server: "Serveur : {{status}}",
			device: "Appareil : {{name}} ({{platform}})",
			noDevice: "Aucun appareil mobile enregistré.",
		},
		today: {
			title: "Aujourd'hui",
			noMissions: "Aucune mission prévue.",
			noRevisions: "Aucune révision due.",
		},
		actions: {
			registerDemo: "Enregistrer un appareil démo",
			sync: "Synchroniser",
		},
		errors: {
			loadFailed: "Impossible de charger les paramètres mobile.",
			registerFailed: "Impossible d'enregistrer l'appareil.",
			syncFailed: "Échec de la synchronisation.",
		},
	},
};
