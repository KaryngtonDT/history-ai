export const browserEn = {
	browser: {
		title: "Shadow Browser",
		description:
			"Configure the Shadow browser companion — platform detection, site permissions, reading context, and explainability.",
		whatCanIDo:
			"Connect the browser session, inspect platforms, manage per-site permissions, review activity, and preview reading companion context.",
		tabs: {
			browser: "Browser",
		},
		status: {
			title: "Browser session",
			active: "Connected ({{state}})",
			inactive: "Not connected",
			connect: "Connect browser",
			disconnect: "Disconnect",
			lastActive: "Last active: {{time}}",
		},
		platform: {
			title: "Platform inspector",
			urlPlaceholder: "https://www.youtube.com/watch?v=...",
			detect: "Detect platform",
			result: "Detected {{platform}} on {{host}}",
		},
		permissions: {
			title: "Permission center",
			siteAllowed: "Site allowed",
			save: "Save site permissions",
			noYoutube: "No youtube.com policy yet — save to create one.",
			allowed: "Allowed",
			blocked: "Blocked",
		},
		history: {
			title: "Activity history",
			empty: "No browser activity yet.",
		},
		explain: {
			title: "Why did Shadow act?",
			reason: "Reason: {{reason}}",
			detail: "{{detail}}",
			refresh: "Refresh explanation",
		},
		reading: {
			title: "Reading companion demo",
			description:
				"Simulate text selection on a reading page. Shadow uses this context for ask, search, and resume flows.",
			url: "Page URL",
			pageTitle: "Page title",
			selection: "Selected text",
			send: "Send context",
			contextPreview:
				'Context updated — platform: {{platform}}, selection: "{{selection}}"',
		},
		youtube: {
			title: "YouTube companion",
			description:
				"For video learning, open Shadow Watch mode — the companion uses transcript and timeline context instead of page selection.",
			openWatch: "Open Shadow Watch mode",
		},
		extension: {
			title: "Browser extension",
			description:
				"Install the Shadow browser extension to connect real tabs, capture selection, and sync context automatically.",
			comingSoon: "Extension packaging in a future sprint",
		},
		errors: {
			loadFailed: "Could not load browser settings.",
		},
	},
};
