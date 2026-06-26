import { env } from "./env";

export const API_BASE_URL = env.apiBaseUrl;

export const CONTENTS_PATH = "/api/contents";

export const PROCESSING_JOBS_PATH = "/api/processing-jobs";

export function contentArtifactsPath(contentId: string): string {
	return `${CONTENTS_PATH}/${contentId}/artifacts`;
}
