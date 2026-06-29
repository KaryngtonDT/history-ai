import { useEffect, useRef } from "react";
import { Card } from "@/components/ui/Card";
import type { TranscriptSegment } from "@/services/transcript/types";
import { formatTranscriptTimestamp } from "@/services/transcript/types";
import styles from "./TranscriptTimeline.module.css";

interface TranscriptTimelineProps {
	segments: TranscriptSegment[];
	activeIndex: number;
	onSegmentSelect: (index: number) => void;
}

export function TranscriptTimeline({
	segments,
	activeIndex,
	onSegmentSelect,
}: TranscriptTimelineProps) {
	const listRef = useRef<HTMLDivElement>(null);

	useEffect(() => {
		const container = listRef.current;

		if (!container) {
			return;
		}

		const activeElement = container.querySelector(
			`[data-segment-index="${activeIndex}"]`,
		);

		if (
			activeElement instanceof HTMLElement &&
			typeof activeElement.scrollIntoView === "function"
		) {
			activeElement.scrollIntoView({ block: "nearest", behavior: "smooth" });
		}
	}, [activeIndex]);

	return (
		<Card className={styles.card}>
			<div ref={listRef} className={styles.list}>
				{segments.map((segment) => {
					const isActive = segment.index === activeIndex;

					return (
						<button
							key={segment.index}
							type="button"
							data-segment-index={segment.index}
							className={isActive ? styles.segmentActive : styles.segment}
							onClick={() => onSegmentSelect(segment.index)}
						>
							<span className={styles.timestamp}>
								{formatTranscriptTimestamp(segment.startTime)} –{" "}
								{formatTranscriptTimestamp(segment.endTime)}
							</span>
							<span className={styles.text}>{segment.text}</span>
						</button>
					);
				})}
			</div>
		</Card>
	);
}
