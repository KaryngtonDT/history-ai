import type { Timeline } from "./types";

export interface TimelineRepository {
	getTimeline(artifactId: string): Promise<Timeline | null>;
}
