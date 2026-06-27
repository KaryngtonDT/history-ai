import type { TimelineEvent } from "./TimelineEvent";

export interface TimelineSection {
	readonly title: string;
	readonly events: readonly TimelineEvent[];
}

export function createTimelineSection(
	title: string,
	events: readonly TimelineEvent[],
): TimelineSection {
	return { title, events };
}
