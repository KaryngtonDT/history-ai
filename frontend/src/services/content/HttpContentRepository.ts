import { CONTENTS_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ContentApiItem, CreateContentApiResponse } from "./apiTypes";
import type { ContentRepository } from "./ContentRepository";
import { mapContentFromApi } from "./mapContentFromApi";
import { mapSourceTypeToApi } from "./mapSourceType";
import type { Content, CreateContentInput, CreateContentResult } from "./types";

/**
 * HTTP adapter for Symfony Content API.
 * @see GET  /api/contents
 * @see POST /api/contents
 */
export class HttpContentRepository implements ContentRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listContents(): Promise<Content[]> {
		const items = await this.httpClient.get<ContentApiItem[]>(CONTENTS_PATH);
		return items.map(mapContentFromApi);
	}

	async createContent(input: CreateContentInput): Promise<CreateContentResult> {
		const data = await this.httpClient.post<CreateContentApiResponse>(
			CONTENTS_PATH,
			{
				title: input.title,
				sourceType: mapSourceTypeToApi(input.sourceType),
			},
		);
		return { id: data.id };
	}
}
