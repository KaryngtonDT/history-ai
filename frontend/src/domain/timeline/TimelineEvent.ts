export interface TimelineEvent {
	readonly text: string;
}

export function createTimelineEvent(text: string): TimelineEvent {
	return { text };
}
