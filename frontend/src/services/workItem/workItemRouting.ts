import type { WorkItemType } from "./types";

export function resolveWorkItemRoute(type: WorkItemType, id: string): string {
	switch (type) {
		case "video":
			return `/video/${id}`;
		case "pdf":
			return `/processing/${id}`;
		case "audio":
			return `/audio/${id}`;
		case "youtube":
			return `/video/${id}`;
		case "project":
			return `/workspace`;
		default:
			return "/";
	}
}

export function workItemFallbackMessage(type: WorkItemType): string {
	switch (type) {
		case "video":
			return "Upload or open a video to continue.";
		case "pdf":
			return "Import a PDF to start knowledge processing.";
		case "audio":
			return "Upload audio to generate a transcript.";
		case "youtube":
			return "Link a YouTube source from import.";
		case "project":
			return "Create a project in workspace.";
		default:
			return "Start from Home to create new work.";
	}
}
