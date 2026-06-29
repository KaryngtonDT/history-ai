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

export function contentGraphArtifactNeighborhoodPath(
	contentId: string,
	artifactId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/graph/artifacts/${artifactId}/neighborhood`;
}

export function contentArtifactRecommendationsPath(
	contentId: string,
	artifactId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/artifacts/${artifactId}/recommendations`;
}

export function contentSemanticSearchPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/semantic-search`;
}

export function contentChatPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/chat`;
}

export function contentChatStreamPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/chat/stream`;
}

export function contentConversationChatPath(
	contentId: string,
	conversationId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/conversations/${conversationId}/chat`;
}

export function contentConversationChatStreamPath(
	contentId: string,
	conversationId: string,
): string {
	return `${CONTENTS_PATH}/${contentId}/conversations/${conversationId}/chat/stream`;
}

export function conversationDocumentsPath(conversationId: string): string {
	return `/api/conversations/${conversationId}/documents`;
}

export function conversationGraphPath(conversationId: string): string {
	return `/api/conversations/${conversationId}/graph`;
}

export function collectionItemsPath(collectionId: string): string {
	return `${COLLECTIONS_PATH}/${collectionId}/items`;
}
