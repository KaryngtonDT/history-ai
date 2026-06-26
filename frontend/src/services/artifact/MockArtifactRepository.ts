import { artifactMocksByContentId } from "@/mock/artifact";
import type { ArtifactRepository } from "./ArtifactRepository";
import type { Artifact } from "./types";

export class MockArtifactRepository implements ArtifactRepository {
	async listByContentId(contentId: string): Promise<Artifact[]> {
		return (artifactMocksByContentId[contentId] ?? []).map((item) => ({
			...item,
		}));
	}
}

export class EmptyMockArtifactRepository implements ArtifactRepository {
	async listByContentId(_contentId: string): Promise<Artifact[]> {
		return [];
	}
}
