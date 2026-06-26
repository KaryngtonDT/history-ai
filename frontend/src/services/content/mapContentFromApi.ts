import type { ContentApiItem } from "./apiTypes";
import { mapSourceTypeFromApi } from "./mapSourceType";
import type { Content, ContentDisplayStatus } from "./types";

function mapStatusFromApi(status: string): ContentDisplayStatus {
	return status === "completed" ? "completed" : "processing";
}

function mapProgressFromApi(status: string): number {
	return status === "completed" ? 100 : 0;
}

export function mapContentFromApi(item: ContentApiItem): Content {
	return {
		id: item.id,
		title: item.title,
		sourceType: mapSourceTypeFromApi(item.sourceType),
		status: mapStatusFromApi(item.status),
		progress: mapProgressFromApi(item.status),
	};
}
