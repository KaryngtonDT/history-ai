import {
	createTimeline,
	createTimelineEvent,
	createTimelineSection,
	type Timeline,
	type TimelineEvent,
	type TimelineSection,
} from "@/domain/timeline";

export type { Timeline, TimelineEvent, TimelineSection };

export interface TimelineEventApiDto {
	text: string;
}

export interface TimelineSectionApiDto {
	title: string;
	events: TimelineEventApiDto[];
}

export interface TimelineApiDto {
	sections: TimelineSectionApiDto[];
}

export function mapTimelineFromApi(dto: TimelineApiDto): Timeline {
	return createTimeline(
		dto.sections.map((section) =>
			createTimelineSection(
				section.title,
				section.events.map((event) => createTimelineEvent(event.text)),
			),
		),
	);
}

export function createEmptyTimeline(): Timeline {
	return createTimeline([]);
}
