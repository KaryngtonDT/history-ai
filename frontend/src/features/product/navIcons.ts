export const NAV_ICONS: Record<string, string> = {
	dashboard: "🏠",
	upload: "🎥",
	import: "📄",
	workspace: "📁",
	"pipeline-settings": "⚙️",
	"ai-engines": "🤖",
	transcript: "📜",
	translations: "🌍",
	audio: "🔊",
	"voice-clone": "🎙",
	"lip-sync": "🎬",
	render: "🎞",
	library: "📚",
	collections: "🗂",
	"settings-hub": "🔧",
};

export const RESULTS_EMPTY_HINTS: Record<
	string,
	{ reason: string; action: string; actionRoute: string }
> = {
	transcript: {
		reason: "No transcript yet.",
		action: "Upload a video",
		actionRoute: "/video/upload",
	},
	translations: {
		reason: "No translations yet.",
		action: "Open a video",
		actionRoute: "/video/upload",
	},
	audio: {
		reason: "No audio generated yet.",
		action: "Open a video",
		actionRoute: "/video/upload",
	},
	"voice-clone": {
		reason: "No cloned voice yet.",
		action: "Generate audio first",
		actionRoute: "/video/upload",
	},
	"lip-sync": {
		reason: "No lip sync preview yet.",
		action: "Complete prior steps",
		actionRoute: "/video/upload",
	},
	render: {
		reason: "No final video yet.",
		action: "Run the pipeline",
		actionRoute: "/video/upload",
	},
};
