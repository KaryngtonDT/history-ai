import { contentGraphPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { GraphRepository } from "./GraphRepository";
import {
	EMPTY_KNOWLEDGE_GRAPH,
	type KnowledgeGraph,
	type KnowledgeGraphApiDto,
	mapKnowledgeGraphFromApi,
} from "./types";

export class HttpGraphRepository implements GraphRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getKnowledgeGraph(contentId: string): Promise<KnowledgeGraph> {
		try {
			const dto = await this.httpClient.get<KnowledgeGraphApiDto>(
				contentGraphPath(contentId),
			);

			return mapKnowledgeGraphFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return EMPTY_KNOWLEDGE_GRAPH;
			}

			throw error;
		}
	}
}
