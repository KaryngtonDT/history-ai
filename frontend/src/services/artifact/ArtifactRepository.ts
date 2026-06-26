import type { Artifact } from "./types";

export interface ArtifactRepository {
	listByContentId(contentId: string): Promise<Artifact[]>;
}
