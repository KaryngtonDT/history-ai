import { createTimeline } from "./Timeline";
import { createTimelineEvent } from "./TimelineEvent";
import { createTimelineSection } from "./TimelineSection";
import type { Timeline } from "./Timeline";
import type { TimelineSection } from "./TimelineSection";

const DEFAULT_SECTION_TITLE = "Timeline";

export function parseTimeline(markdown: string): Timeline {
	const sections: TimelineSection[] = [];
	let current: TimelineSection | null = null;

	for (const line of markdown.split("\n")) {
		if (line.startsWith("# ") && !line.startsWith("## ")) {
			continue;
		}

		if (line.startsWith("## ")) {
			if (current !== null) {
				sections.push(current);
			}

			current = createTimelineSection(line.slice(3), []);
			continue;
		}

		if (line.startsWith("- ")) {
			if (current === null) {
				current = createTimelineSection(DEFAULT_SECTION_TITLE, []);
			}

			const event = createTimelineEvent(line.slice(2));
			current = createTimelineSection(current.title, [
				...current.events,
				event,
			]);
		}
	}

	if (current !== null) {
		sections.push(current);
	}

	return createTimeline(sections);
}
