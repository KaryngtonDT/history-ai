export interface CommandItem {
	id: string;
	label: string;
	description: string;
	path: string;
	group: string;
	keywords: string[];
}

export const COMMAND_ITEMS: CommandItem[] = [
	{
		id: "dashboard",
		label: "Dashboard",
		description: "Overview of recent work",
		path: "/",
		group: "Navigate",
		keywords: ["home", "overview"],
	},
	{
		id: "upload",
		label: "Upload Video",
		description: "Start video localization pipeline",
		path: "/video/upload",
		group: "Create",
		keywords: ["video", "import", "upload"],
	},
	{
		id: "workspace",
		label: "Workspace",
		description: "Projects, batch, team, analytics",
		path: "/workspace",
		group: "Create",
		keywords: ["project", "batch", "team"],
	},
	{
		id: "transcript",
		label: "Open Transcript",
		description: "Requires video in URL context",
		path: "/video/upload",
		group: "Results",
		keywords: ["transcript", "speech", "stt"],
	},
	{
		id: "translations",
		label: "Open Translations",
		description: "View translated languages",
		path: "/video/upload",
		group: "Results",
		keywords: ["translation", "language"],
	},
	{
		id: "audio",
		label: "Open Audio",
		description: "Preview generated audio",
		path: "/video/upload",
		group: "Results",
		keywords: ["tts", "audio", "speech"],
	},
	{
		id: "pipeline",
		label: "Pipeline Configuration",
		description: "Configure AI engines per stage",
		path: "/settings/pipeline",
		group: "Settings",
		keywords: ["pipeline", "engines", "stages"],
	},
	{
		id: "ai",
		label: "AI Engines",
		description: "View available providers",
		path: "/settings/ai",
		group: "Settings",
		keywords: ["ai", "providers", "engines"],
	},
	{
		id: "library",
		label: "Library",
		description: "Browse saved content",
		path: "/library",
		group: "Library",
		keywords: ["library", "saved"],
	},
	{
		id: "analytics",
		label: "Workspace Analytics",
		description: "Telemetry and provider statistics",
		path: "/workspace",
		group: "Review",
		keywords: ["analytics", "telemetry", "metrics"],
	},
	{
		id: "import",
		label: "Import Documents",
		description: "PDF and audio import",
		path: "/import",
		group: "Create",
		keywords: ["pdf", "import", "document"],
	},
];

export function filterCommandItems(query: string): CommandItem[] {
	const normalized = query.trim().toLowerCase();

	if (!normalized) {
		return COMMAND_ITEMS;
	}

	return COMMAND_ITEMS.filter(
		(item) =>
			item.label.toLowerCase().includes(normalized) ||
			item.description.toLowerCase().includes(normalized) ||
			item.keywords.some((keyword) => keyword.includes(normalized)),
	);
}
