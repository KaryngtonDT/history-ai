import { contentRelationsPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { RelationRepository } from "./RelationRepository";
import {
	type ArtifactRelation,
	type ArtifactRelationsApiDto,
	mapArtifactRelationsFromApi,
} from "./types";

export class HttpRelationRepository implements RelationRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getArtifactRelations(contentId: string): Promise<ArtifactRelation[]> {
		try {
			const dto = await this.httpClient.get<ArtifactRelationsApiDto>(
				contentRelationsPath(contentId),
			);

			return mapArtifactRelationsFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return [];
			}

			throw error;
		}
	}
}
