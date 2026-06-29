import {
	contentGraphArtifactNeighborhoodPath,
	contentGraphPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { GraphRepository } from "./GraphRepository";
import {
	EMPTY_KNOWLEDGE_GRAPH,
	type GraphNeighborhood,
	type GraphNeighborhoodApiDto,
	type KnowledgeGraph,
	type KnowledgeGraphApiDto,
	mapGraphNeighborhoodFromApi,
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

	async getGraphNeighborhood(
		contentId: string,
		artifactId: string,
	): Promise<GraphNeighborhood | null> {
		try {
			const dto = await this.httpClient.get<GraphNeighborhoodApiDto>(
				contentGraphArtifactNeighborhoodPath(contentId, artifactId),
			);

			return mapGraphNeighborhoodFromApi(dto);
		} catch (error) {
			if (
				error instanceof ApiError &&
				(error.status === 400 || error.status === 404)
			) {
				return null;
			}

			throw error;
		}
	}
}
