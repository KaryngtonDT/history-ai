import { contentArtifactRecommendationsPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { RecommendationRepository } from "./RecommendationRepository";
import {
	type ArtifactRecommendationsApiDto,
	mapArtifactRecommendationsFromApi,
	type RecommendedArtifact,
} from "./types";

export class HttpRecommendationRepository implements RecommendationRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getArtifactRecommendations(
		contentId: string,
		artifactId: string,
	): Promise<RecommendedArtifact[]> {
		try {
			const dto = await this.httpClient.get<ArtifactRecommendationsApiDto>(
				contentArtifactRecommendationsPath(contentId, artifactId),
			);

			return mapArtifactRecommendationsFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return [];
			}

			throw error;
		}
	}
}
