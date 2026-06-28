import { env } from "./env";

export const API_BASE_URL = env.apiBaseUrl;

export const CONTENTS_PATH = "/api/contents";

export const PROCESSING_JOBS_PATH = "/api/processing-jobs";

export const LIBRARY_ITEMS_PATH = "/api/library/items";

export const COLLECTIONS_PATH = "/api/collections";

export const SEARCH_LIBRARY_PATH = "/api/search/library";

export function timelinePath(artifactId: string): string {
	return `/api/timeline/${artifactId}`;
}

export function timelineMapPath(artifactId: string): string {
	return `/api/maps/timeline/${artifactId}`;
}

export function contentArtifactsPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/artifacts`;
}

export function contentRelationsPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/relations`;
}

export function contentGraphPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/graph`;
}

export function collectionItemsPath(collectionId: string): string {
	return `${COLLECTIONS_PATH}/${collectionId}/items`;
}
