export const shellEn = {
	shell: {
		brand: {
			title: "Lumen",
			subtitle:
				"AI platform to transform your content into knowledge and understanding.",
			tagline: "AI-guided comprehension",
		},
		sidebar: {
			ariaLabel: "Product navigation",
			shortcut: "Press {{modifier}}+K to search anywhere",
		},
		breadcrumbs: {
			ariaLabel: "Breadcrumb",
			home: "Home",
			import: "Import",
			video: "Video",
			upload: "Upload",
			transcript: "Transcript",
			translations: "Translations",
			audio: "Audio",
			youtube: "YouTube",
			"voice-clone": "Cloned Voice",
			"lip-sync": "Lip Sync Preview",
			render: "Final Video",
			watch: "Shadow Watch",
			workspace: "Workspace",
			library: "Library",
			collections: "Collections",
			settings: "Settings",
			ai: "AI Models",
			pipeline: "Pipeline Setup",
			processing: "Processing",
			overview: "Overview",
			videoId: "Video {{id}}…",
		},
		pageIntro: {
			whatCanIDo: "What can I do here?",
		},
		nav: {
			groups: {
				create: "Create",
				pipeline: "AI Pipeline",
				results: "Results",
				library: "Library",
				settings: "Settings",
			},
			items: {
				dashboard: {
					label: "Home",
					description: "Orientation, create, and recent work.",
				},
				upload: {
					label: "Upload Video",
					description: "Upload a video and start the AI pipeline.",
				},
				import: {
					label: "Import Documents",
					description: "Import PDF or audio for knowledge processing.",
				},
				workspace: {
					label: "Workspace",
					description: "Projects, batch processing, team, and analytics.",
				},
				"pipeline-settings": {
					label: "Pipeline Setup",
					description: "Choose AI engines per processing stage.",
				},
				"ai-engines": {
					label: "AI Models",
					description: "View available AI providers and capabilities.",
				},
				transcript: { label: "Transcript" },
				translations: { label: "Translations" },
				audio: { label: "Audio" },
				"voice-clone": { label: "Cloned Voice" },
				"lip-sync": { label: "Lip Sync Preview" },
				render: { label: "Final Video" },
				shadow: { label: "Shadow" },
				library: { label: "Library" },
				collections: { label: "Collections" },
				"settings-hub": {
					label: "Settings",
					description: "App configuration and AI pipeline settings.",
				},
			},
			empty: {
				transcript: {
					reason: "No transcript yet.",
					why: "Transcripts unlock translation, audio, and knowledge search.",
					action: "Upload video",
				},
				translations: {
					reason: "No translations yet.",
					why: "Translations localize your content for each target language.",
					action: "Open a video",
				},
				audio: {
					reason: "No audio generated yet.",
					why: "Generated speech powers voice clone and lip sync steps.",
					action: "Open a video",
				},
				"voice-clone": {
					reason: "No cloned voice yet.",
					why: "Cloned voice keeps the original speaker identity in localized audio.",
					action: "Generate audio first",
				},
				"lip-sync": {
					reason: "No lip sync preview yet.",
					why: "Lip sync aligns mouth movement with localized speech before final render.",
					action: "Complete prior steps",
				},
				render: {
					reason: "No final video yet.",
					why: "The final render produces a downloadable localized MP4.",
					action: "Run the pipeline",
				},
				shadow: {
					reason: "Shadow watch not started.",
					why: "Shadow helps you learn while watching with contextual Q&A.",
					action: "Open a video",
				},
			},
		},
	},
	home: {
		eyebrow: "Home",
		title: "Lumen",
		description: "Transform knowledge into understanding.",
		whatCanIDo:
			"Upload a video, import a document, or resume work in progress.",
		loading: "Loading home",
		loadError:
			"Could not load your recent work. Check that the backend is running.",
		errorTitle: "Unable to load home",
		create: {
			heading: "What do you want to transform?",
			nextPrefix: "Next:",
			ariaLabel: "Create {{type}}",
			video: {
				label: "Video",
				description:
					"Upload and translate a video with AI voice, lip sync, and render.",
				nextStep: "Choose languages and AI mode after upload.",
			},
			pdf: {
				label: "PDF",
				description: "Import documents for knowledge extraction and chat.",
				nextStep: "Processing creates summaries, graph, and library items.",
			},
			audio: {
				label: "Audio",
				description: "Import audio to generate transcripts and insights.",
				nextStep: "Transcript becomes available for review.",
			},
			youtube: {
				label: "YouTube",
				description: "Link a YouTube source for processing.",
				nextStep: "Runs the full video localization pipeline.",
			},
		},
		continue: {
			heading: "Continue your work",
			currentStep: "Current step:",
		},
		recent: {
			heading: "Recent work",
			empty: "No work yet. Upload a video or import a document to get started.",
			open: "Open →",
			openAria: "Open {{title}}",
		},
		stats: {
			heading: "At a glance",
			videos: {
				label: "Videos",
				description: "Active video work",
				action: "View workspace →",
			},
			projects: {
				label: "Projects",
				description: "Batch and team projects",
				action: "Open projects →",
			},
			completed: {
				label: "Completed",
				description: "Finished work items",
				action: "Open library →",
			},
			artifacts: {
				label: "Artifacts",
				description: "Generated outputs",
				action: "Browse artifacts →",
			},
			ariaLabel: "{{label}}: {{count}}. {{action}}",
		},
		aiDirector: {
			heading: "AI Director",
			loading: "Loading recommendation",
			recommended: "Recommended workflow:",
			configureLink: "Configure on upload →",
			empty:
				"Upload a video in automatic mode to see AI pipeline recommendations.",
			uploadLink: "Upload video →",
		},
	},
	guidance: {
		palette: {
			closeAria: "Close command palette",
			dialogAria: "Command palette",
			placeholder: "Search videos, projects, pipeline, analytics…",
			empty: "No matching commands.",
			footer: "{{count}} commands · Esc to close",
			groups: {
				Navigate: "Navigate",
				Create: "Create",
				Results: "Results",
				Settings: "Settings",
				Library: "Library",
				Review: "Review",
			},
		},
		commands: {
			dashboard: {
				label: "Home",
				description: "Orientation and recent work",
			},
			upload: {
				label: "Upload Video",
				description: "Start video localization pipeline",
			},
			workspace: {
				label: "Workspace",
				description: "Projects, batch, team, analytics",
			},
			transcript: {
				label: "Open Transcript",
				description: "Requires video in URL context",
			},
			translations: {
				label: "Open Translations",
				description: "View translated languages",
			},
			audio: {
				label: "Open Audio",
				description: "Preview generated audio",
			},
			pipeline: {
				label: "Pipeline Configuration",
				description: "Configure AI engines per stage",
			},
			ai: {
				label: "AI Engines",
				description: "View available providers",
			},
			library: {
				label: "Library",
				description: "Browse saved content",
			},
			analytics: {
				label: "Workspace Analytics",
				description: "Telemetry and provider statistics",
			},
			import: {
				label: "Import Documents",
				description: "PDF and audio import",
			},
		},
	},
	settings: {
		eyebrow: "Settings",
		title: "Settings",
		description: "Configure how Lumen processes your videos.",
		whatCanIDo:
			"Choose AI engines and pipeline stages. Changes apply to the next processing run.",
		language: {
			title: "Interface language",
			description: "Choose the language for menus, labels, and help text.",
		},
		aiEngines: {
			title: "AI Engines",
			description: "View registered providers and capabilities (Sprint 34).",
		},
		pipeline: {
			title: "Pipeline Configuration",
			description: "Assign engines to each processing stage (Sprint 39).",
		},
		learning: {
			title: "Adaptive Learning",
			description: "Review and control Lumen's deterministic learning profile.",
		},
		shadow: {
			title: "Shadow Identity",
			description:
				"Persona, voice studio, language composer, and teach-by-voice controls.",
		},
	},
	workItem: {
		types: {
			video: "Video",
			pdf: "PDF",
			audio: "Audio",
			youtube: "YouTube",
			project: "Project",
		},
		statuses: {
			processing: "Processing",
			completed: "Completed",
			pending: "Pending",
			failed: "Failed",
			ready: "Ready",
		},
	},
	help: {
		explain: {
			defaultLabel: "Explain this",
			dialogAria: "Feature explanation",
		},
		academy: {
			readingTime: "Estimated reading: {{minutes}} min",
			sections: {
				whatIsIt: "What is it?",
				details: "Details",
				bestPractice: "Best practice",
				commonMistake: "Common mistake",
				nextStep: "Next step",
				faq: "FAQ",
			},
		},
		tooltip: {
			defaultLabel: "?",
			ariaLabel: "Help: {{title}}",
		},
	},
} as const;
