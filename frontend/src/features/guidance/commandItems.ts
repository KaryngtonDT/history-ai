export interface CommandItem {
	id: string;
	path: string;
	group: string;
	keywords: string[];
}

export const COMMAND_ITEMS: CommandItem[] = [
	{
		id: "dashboard",
		path: "/",
		group: "Navigate",
		keywords: ["home", "overview", "dashboard"],
	},
	{
		id: "upload",
		path: "/video/upload",
		group: "Create",
		keywords: ["video", "import", "upload"],
	},
	{
		id: "workspace",
		path: "/workspace",
		group: "Create",
		keywords: ["project", "batch", "team"],
	},
	{
		id: "transcript",
		path: "/video/upload",
		group: "Results",
		keywords: ["transcript", "speech", "stt"],
	},
	{
		id: "translations",
		path: "/video/upload",
		group: "Results",
		keywords: ["translation", "language"],
	},
	{
		id: "audio",
		path: "/video/upload",
		group: "Results",
		keywords: ["tts", "audio", "speech"],
	},
	{
		id: "pipeline",
		path: "/settings/pipeline",
		group: "Settings",
		keywords: ["pipeline", "engines", "stages"],
	},
	{
		id: "ai",
		path: "/settings/ai",
		group: "Settings",
		keywords: ["ai", "providers", "engines"],
	},
	{
		id: "library",
		path: "/library",
		group: "Library",
		keywords: ["library", "saved"],
	},
	{
		id: "analytics",
		path: "/workspace",
		group: "Review",
		keywords: ["analytics", "telemetry", "metrics"],
	},
	{
		id: "import",
		path: "/import",
		group: "Create",
		keywords: ["pdf", "import", "document"],
	},
];

export function filterCommandItems(
	query: string,
	translate: (key: string) => string,
): CommandItem[] {
	const normalized = query.trim().toLowerCase();

	if (!normalized) {
		return COMMAND_ITEMS;
	}

	return COMMAND_ITEMS.filter((item) => {
		const label = translate(`guidance.commands.${item.id}.label`).toLowerCase();
		const description = translate(
			`guidance.commands.${item.id}.description`,
		).toLowerCase();

		return (
			label.includes(normalized) ||
			description.includes(normalized) ||
			item.keywords.some((keyword) => keyword.includes(normalized))
		);
	});
}
