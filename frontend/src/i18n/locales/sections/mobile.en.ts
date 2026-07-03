export const mobileEn = {
	mobile: {
		title: "Shadow Mobile",
		description:
			"Configure the Shadow mobile companion — connection profile, today dashboard, and sync with your home server.",
		whatCanIDo:
			"Register a device, review today's missions and revisions, and manage Personal Remote access via Tailscale.",
		tabs: {
			mobile: "Mobile",
		},
		status: {
			title: "Mobile connection",
			connected: "Shadow connected",
			disconnected: "Not connected",
			mode: "Mode: {{mode}}",
			server: "Server: {{status}}",
			device: "Device: {{name}} ({{platform}})",
			noDevice: "No mobile device registered yet.",
		},
		today: {
			title: "Today",
			noMissions: "No missions scheduled.",
			noRevisions: "No revisions due.",
		},
		actions: {
			registerDemo: "Register demo device",
			sync: "Sync now",
		},
		errors: {
			loadFailed: "Could not load mobile companion settings.",
			registerFailed: "Could not register device.",
			syncFailed: "Sync failed.",
		},
	},
};
