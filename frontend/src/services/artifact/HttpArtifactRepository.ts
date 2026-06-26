import { contentArtifactsPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { ArtifactRepository } from "./ArtifactRepository";
import type { Artifact, ArtifactApiDto } from "./types";
import { mapArtifactFromApi } from "./types";

export class HttpArtifactRepository implements ArtifactRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listByContentId(contentId: string): Promise<Artifact[]> {
		const items = await this.httpClient.get<ArtifactApiDto[]>(
			contentArtifactsPath(contentId),
		);

		return items.map(mapArtifactFromApi);
	}
}
