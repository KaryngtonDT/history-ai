import type { ArtifactRepository } from "./ArtifactRepository";
import { createArtifactRepository } from "./ArtifactRepositoryFactory";
import type { Artifact } from "./types";

export class ArtifactService {
	private readonly repository: ArtifactRepository;

	constructor(repository: ArtifactRepository) {
		this.repository = repository;
	}

	listByContentId(contentId: string): Promise<Artifact[]> {
		return this.repository.listByContentId(contentId);
	}

	async getSummaryArtifact(contentId: string): Promise<Artifact | null> {
		const artifacts = await this.repository.listByContentId(contentId);
		return artifacts.find((artifact) => artifact.type === "summary") ?? null;
	}

	async getTranscriptArtifact(contentId: string): Promise<Artifact | null> {
		const artifacts = await this.repository.listByContentId(contentId);
		return artifacts.find((artifact) => artifact.type === "transcript") ?? null;
	}
}

export const artifactService = new ArtifactService(createArtifactRepository());
