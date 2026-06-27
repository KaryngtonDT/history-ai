import type { TimelineRepository } from "./TimelineRepository";
import { createTimelineRepository } from "./TimelineRepositoryFactory";
import type { Timeline } from "./types";

const ARTIFACT_ID_PATTERN =
	/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i;

export class TimelineService {
	private readonly repository: TimelineRepository;

	constructor(repository: TimelineRepository) {
		this.repository = repository;
	}

	getTimeline(artifactId: string): Promise<Timeline | null> {
		const normalized = artifactId.trim();

		if (normalized === "" || !ARTIFACT_ID_PATTERN.test(normalized)) {
			return Promise.resolve(null);
		}

		return this.repository.getTimeline(normalized);
	}
}

export const timelineService = new TimelineService(createTimelineRepository());
