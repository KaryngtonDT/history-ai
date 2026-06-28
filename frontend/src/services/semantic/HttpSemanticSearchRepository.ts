import { contentSemanticSearchPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { SemanticSearchRepository } from "./SemanticSearchRepository";
import {
	mapSemanticSearchFromApi,
	type RetrievedChunk,
	type SemanticSearchApiDto,
} from "./types";

export class HttpSemanticSearchRepository implements SemanticSearchRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async searchSemanticChunks(
		contentId: string,
		query: string,
	): Promise<RetrievedChunk[]> {
		try {
			const path = `${contentSemanticSearchPath(contentId)}?q=${encodeURIComponent(query)}`;
			const dto = await this.httpClient.get<SemanticSearchApiDto>(path);

			return mapSemanticSearchFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return [];
			}

			throw error;
		}
	}
}
