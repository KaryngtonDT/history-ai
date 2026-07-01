import type { WorkItemType } from "@/services/workItem/types";

export interface CreateCard {
	id: WorkItemType | "pdf";
	type: WorkItemType | "pdf";
	label: string;
	description: string;
	nextStep: string;
	route: string;
	icon: string;
	primary?: boolean;
}

export const CREATE_CARDS: CreateCard[] = [
	{
		id: "video",
		type: "video",
		label: "Video",
		description:
			"Upload and translate a video with AI voice, lip sync, and render.",
		nextStep: "Choose languages and AI mode after upload.",
		route: "/video/upload",
		icon: "🎥",
		primary: true,
	},
	{
		id: "pdf",
		type: "pdf",
		label: "PDF",
		description: "Import documents for knowledge extraction and chat.",
		nextStep: "Processing creates summaries, graph, and library items.",
		route: "/import",
		icon: "📄",
	},
	{
		id: "audio",
		type: "audio",
		label: "Audio",
		description: "Import audio to generate transcripts and insights.",
		nextStep: "Transcript becomes available for review.",
		route: "/import",
		icon: "🎤",
	},
	{
		id: "youtube",
		type: "youtube",
		label: "YouTube",
		description: "Link a YouTube source for processing.",
		nextStep: "Use import flow for supported sources.",
		route: "/import",
		icon: "▶️",
	},
];
