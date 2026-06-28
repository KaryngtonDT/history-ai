import { artifactMocksByContentId } from "@/mock/artifact";
import type { RelationRepository } from "./RelationRepository";
import {
	type ArtifactRelation,
	resolveArtifactRelationsFromArtifacts,
} from "./types";

export class MockRelationRepository implements RelationRepository {
	async getArtifactRelations(contentId: string): Promise<ArtifactRelation[]> {
		const artifacts = artifactMocksByContentId[contentId];

		if (artifacts === undefined || artifacts.length === 0) {
			return [];
		}

		return resolveArtifactRelationsFromArtifacts(artifacts);
	}
}
