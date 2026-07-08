export type NavItemKind = "link" | "contextual";

export interface ProductNavItem {
	id: string;
	label: string;
	to: string;
	end?: boolean;
	kind?: NavItemKind;
	requiresVideoId?: boolean;
	sprint?: number;
	description?: string;
}

export interface ProductNavGroup {
	id: string;
	label: string;
	items: ProductNavItem[];
}

export const PRODUCT_NAV_GROUPS: ProductNavGroup[] = [
	{
		id: "create",
		label: "Create",
		items: [
			{
				id: "dashboard",
				label: "Home",
				to: "/",
				end: true,
				description: "Orientation, create, and recent work.",
			},
			{
				id: "upload",
				label: "Upload Video",
				to: "/video/upload",
				sprint: 31,
				description: "Upload a video and start the AI pipeline.",
			},
			{
				id: "import",
				label: "Import Documents",
				to: "/import",
				description: "Import PDF or audio for knowledge processing.",
			},
			{
				id: "workspace",
				label: "Workspace",
				to: "/workspace",
				sprint: 45,
				description: "Projects, batch processing, team, and analytics.",
			},
		],
	},
	{
		id: "pipeline",
		label: "AI Pipeline",
		items: [
			{
				id: "pipeline-settings",
				label: "Pipeline Setup",
				to: "/settings/pipeline",
				sprint: 39,
				description: "Choose AI engines per processing stage.",
			},
			{
				id: "runtime-engines",
				label: "Engine Manager",
				to: "/settings/runtime/engines",
				sprint: 72,
				description:
					"Install, benchmark, and select AI engines per capability.",
			},
			{
				id: "runtime-settings",
				label: "Runtime Center",
				to: "/settings/runtime",
				sprint: 70,
				description: "Discover, verify, and benchmark AI engines.",
			},
			{
				id: "ai-engines",
				label: "AI Models",
				to: "/settings/ai",
				sprint: 34,
				description: "View available AI providers and capabilities.",
			},
		],
	},
	{
		id: "results",
		label: "Results",
		items: [
			{
				id: "transcript",
				label: "Transcript",
				to: "/video/:videoId/transcript",
				kind: "contextual",
				requiresVideoId: true,
				sprint: 32,
			},
			{
				id: "translations",
				label: "Translations",
				to: "/video/:videoId/translations",
				kind: "contextual",
				requiresVideoId: true,
				sprint: 33,
			},
			{
				id: "audio",
				label: "Audio",
				to: "/video/:videoId/audio",
				kind: "contextual",
				requiresVideoId: true,
				sprint: 35,
			},
			{
				id: "voice-clone",
				label: "Cloned Voice",
				to: "/video/:videoId/voice-clone",
				kind: "contextual",
				requiresVideoId: true,
				sprint: 36,
			},
			{
				id: "lip-sync",
				label: "Lip Sync Preview",
				to: "/video/:videoId/lip-sync",
				kind: "contextual",
				requiresVideoId: true,
				sprint: 37,
			},
			{
				id: "render",
				label: "Final Video",
				to: "/video/:videoId/render",
				kind: "contextual",
				requiresVideoId: true,
				sprint: 38,
			},
			{
				id: "shadow",
				label: "Shadow",
				to: "/video/:videoId/watch",
				kind: "contextual",
				requiresVideoId: true,
				sprint: 55,
				description: "Watch with Shadow AI companion.",
			},
		],
	},
	{
		id: "library",
		label: "Library",
		items: [
			{ id: "library", label: "Library", to: "/library" },
			{ id: "collections", label: "Collections", to: "/collections" },
		],
	},
	{
		id: "settings",
		label: "Settings",
		items: [
			{
				id: "settings-hub",
				label: "Settings",
				to: "/settings",
				description: "App configuration and AI pipeline settings.",
			},
		],
	},
];

export function resolveNavPath(
	template: string,
	videoId: string | null,
): string {
	if (!template.includes(":videoId")) {
		return template;
	}

	if (!videoId) {
		return "/video/upload";
	}

	return template.replace(":videoId", videoId);
}
