import { useEffect, useState } from "react";
import { useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { transcriptService } from "@/services/transcript/TranscriptService";
import type { VideoTranscript } from "@/services/transcript/types";
import { formatTranscriptTimestamp } from "@/services/transcript/types";
import { TranscriptTimeline } from "../TranscriptTimeline";
import styles from "./TranscriptPanel.module.css";

export function TranscriptPanel() {
	const { videoId = "" } = useParams();
	const [transcript, setTranscript] = useState<VideoTranscript | null>(null);
	const [loading, setLoading] = useState(true);
	const [activeIndex, setActiveIndex] = useState(0);

	useEffect(() => {
		let cancelled = false;

		setLoading(true);
		setTranscript(null);
		setActiveIndex(0);

		transcriptService.getTranscript(videoId).then((result) => {
			if (!cancelled) {
				setTranscript(result);
				setLoading(false);
			}
		});

		return () => {
			cancelled = true;
		};
	}, [videoId]);

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label="Loading transcript" />
			</div>
		);
	}

	if (!transcript) {
		return (
			<div className={styles.root}>
				<EmptyState
					title="Transcript unavailable"
					description="No transcript was found for this video yet."
				/>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>Transcript</h2>
					<p className={styles.meta}>
						Video ID:{" "}
						<span className={styles.videoId}>{transcript.videoId}</span>
					</p>
				</div>
				<Badge variant="info">{transcript.language}</Badge>
			</header>

			<Card className={styles.summary}>
				<p className={styles.summaryLabel}>Full text</p>
				<p className={styles.summaryText}>{transcript.text}</p>
				<p className={styles.summaryMeta}>
					{transcript.segmentCount} segments ·{" "}
					{formatTranscriptTimestamp(transcript.duration)} duration
				</p>
			</Card>

			<TranscriptTimeline
				segments={transcript.segments}
				activeIndex={activeIndex}
				onSegmentSelect={setActiveIndex}
			/>
		</div>
	);
}
