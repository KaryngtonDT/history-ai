import type { Timeline } from "@/domain/timeline";
import styles from "./InteractiveTimeline.module.css";

interface InteractiveTimelineProps {
	timeline: Timeline;
}

export function InteractiveTimeline({ timeline }: InteractiveTimelineProps) {
	if (timeline.sections.length === 0) {
		return (
			<p className={styles.emptyMessage}>No timeline events to display.</p>
		);
	}

	return (
		<div className={styles.root}>
			{timeline.sections.map((section) => (
				<section
					key={section.title}
					className={styles.section}
					aria-labelledby={`timeline-section-${section.title}`}
				>
					<h3
						id={`timeline-section-${section.title}`}
						className={styles.sectionTitle}
					>
						{section.title}
					</h3>
					{section.events.length > 0 ? (
						<ol className={styles.eventList}>
							{section.events.map((event) => (
								<li
									key={`${section.title}-${event.text}`}
									className={styles.event}
								>
									{event.text}
								</li>
							))}
						</ol>
					) : null}
				</section>
			))}
		</div>
	);
}
