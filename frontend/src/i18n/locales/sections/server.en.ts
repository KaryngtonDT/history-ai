export const serverEn = {
	server: {
		title: "Personal server",
		description:
			"Monitor your home Lumen server — Docker health, readiness checks, and Tailscale connection status.",
		whatCanIDo:
			"Verify the server is reachable before using Shadow Mobile away from home.",
		overview: {
			title: "Server overview",
			available: "Available: {{value}}",
			checks: "Checks: {{healthy}}/{{total}} healthy",
			mode: "Connection mode: {{mode}}",
			status: "Status: {{status}} · Live: {{live}}",
		},
		checks: {
			title: "Health checks",
			empty: "No checks reported.",
		},
		errors: {
			loadFailed: "Could not load server dashboard.",
		},
	},
};
