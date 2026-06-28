import type { RelationRepository } from "./RelationRepository";
import { createRelationRepository } from "./RelationRepositoryFactory";
import type { ArtifactRelation } from "./types";

const CONTENT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class RelationService {
	private readonly repository: RelationRepository;

	constructor(repository: RelationRepository) {
		this.repository = repository;
	}

	getArtifactRelations(contentId: string): Promise<ArtifactRelation[]> {
		const normalized = contentId.trim();

		if (normalized === "" || !CONTENT_ID_PATTERN.test(normalized)) {
			return Promise.resolve([]);
		}

		return this.repository.getArtifactRelations(normalized);
	}
}

export const relationService = new RelationService(createRelationRepository());
