import type { WorkItemType } from "@/services/workItem/types";

export interface CreateCard {
	id: WorkItemType | "pdf";
	type: WorkItemType | "pdf";
	route: string;
	icon: string;
	primary?: boolean;
	comingSoon?: boolean;
}

export const CREATE_CARDS: CreateCard[] = [
	{
		id: "video",
		type: "video",
		route: "/video/upload",
		icon: "🎥",
		primary: true,
	},
	{
		id: "pdf",
		type: "pdf",
		route: "/import",
		icon: "📄",
	},
	{
		id: "audio",
		type: "audio",
		route: "/audio/upload",
		icon: "🎤",
	},
	{
		id: "youtube",
		type: "youtube",
		route: "/youtube/import",
		icon: "▶️",
	},
];
