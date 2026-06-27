import { parseTimeline } from "@/domain/timeline";
import { artifactMocksByContentId } from "@/mock/artifact";
import type { TimelineRepository } from "./TimelineRepository";
import type { Timeline } from "./types";

export class MockTimelineRepository implements TimelineRepository {
	async getTimeline(artifactId: string): Promise<Timeline | null> {
		for (const artifacts of Object.values(artifactMocksByContentId)) {
			const artifact = artifacts.find(
				(candidate) =>
					candidate.id === artifactId && candidate.type === "timeline",
			);

			if (artifact !== undefined) {
				return parseTimeline(artifact.content);
			}
		}

		return null;
	}
}
