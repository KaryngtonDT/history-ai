import type { TimelineSection } from "./TimelineSection";

export interface Timeline {
	readonly sections: readonly TimelineSection[];
}

export function createTimeline(sections: readonly TimelineSection[]): Timeline {
	return { sections };
}
