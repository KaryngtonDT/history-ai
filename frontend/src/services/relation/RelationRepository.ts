import type { ArtifactRelation } from "./types";

export interface RelationRepository {
	getArtifactRelations(contentId: string): Promise<ArtifactRelation[]>;
}
