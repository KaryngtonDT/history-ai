import type { ContentApiItem, CreateContentApiResponse } from "./apiTypes";
import { ContentApiError } from "./ContentApiError";
import type { ContentRepository } from "./ContentRepository";
import { mapContentFromApi } from "./mapContentFromApi";
import { mapSourceTypeToApi } from "./mapSourceType";
import type { Content, CreateContentInput, CreateContentResult } from "./types";

const DEFAULT_BASE_URL =
	(typeof import.meta.env.VITE_API_BASE_URL === "string" &&
		import.meta.env.VITE_API_BASE_URL.length > 0 &&
		import.meta.env.VITE_API_BASE_URL) ||
	"/api";

/**
 * HTTP adapter for Symfony Content API.
 * @see GET  /api/contents
 * @see POST /api/contents
 */
export class HttpContentRepository implements ContentRepository {
	private readonly baseUrl: string;

	constructor(baseUrl = DEFAULT_BASE_URL) {
		this.baseUrl = baseUrl.replace(/\/$/, "");
	}

	getBaseUrl(): string {
		return this.baseUrl;
	}

	async listContents(): Promise<Content[]> {
		const response = await fetch(`${this.baseUrl}/contents`, {
			headers: { Accept: "application/json" },
		});

		if (!response.ok) {
			throw new ContentApiError(
				`Failed to list contents (${response.status})`,
				response.status,
			);
		}

		const items = (await response.json()) as ContentApiItem[];
		return items.map(mapContentFromApi);
	}

	async createContent(input: CreateContentInput): Promise<CreateContentResult> {
		const response = await fetch(`${this.baseUrl}/contents`, {
			method: "POST",
			headers: {
				Accept: "application/json",
				"Content-Type": "application/json",
			},
			body: JSON.stringify({
				title: input.title,
				sourceType: mapSourceTypeToApi(input.sourceType),
			}),
		});

		if (!response.ok) {
			throw new ContentApiError(
				`Failed to create content (${response.status})`,
				response.status,
			);
		}

		const data = (await response.json()) as CreateContentApiResponse;
		return { id: data.id };
	}
}
