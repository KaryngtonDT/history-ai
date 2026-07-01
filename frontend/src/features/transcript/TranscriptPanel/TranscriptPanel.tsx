import { useEffect, useState } from "react";
import { useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n/useTranslation";
import { transcriptService } from "@/services/transcript/TranscriptService";
import type { VideoTranscript } from "@/services/transcript/types";
import { formatTranscriptTimestamp } from "@/services/transcript/types";
import { TranscriptTimeline } from "../TranscriptTimeline";
import styles from "./TranscriptPanel.module.css";

export function TranscriptPanel() {
	const { t } = useTranslation();
	const { videoId = "", audioId = "" } = useParams();
	const resourceId = videoId || audioId;
	const [transcript, setTranscript] = useState<VideoTranscript | null>(null);
	const [loading, setLoading] = useState(true);
	const [activeIndex, setActiveIndex] = useState(0);

	useEffect(() => {
		let cancelled = false;

		setLoading(true);
		setTranscript(null);
		setActiveIndex(0);

		transcriptService.getTranscript(resourceId).then((result) => {
			if (!cancelled) {
				setTranscript(result);
				setLoading(false);
			}
		});

		return () => {
			cancelled = true;
		};
	}, [resourceId]);

	if (loading) {
		return (
			<div className={styles.root}>
				<Spinner label={t("pipeline.transcript.loading")} />
			</div>
		);
	}

	if (!transcript) {
		return (
			<div className={styles.root}>
				<EmptyState
					title={t("pipeline.transcript.unavailableTitle")}
					description={t("pipeline.transcript.unavailableDescription")}
				/>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<header className={styles.header}>
				<div>
					<h2 className={styles.title}>{t("pipeline.transcript.title")}</h2>
					<p className={styles.meta}>
						{t("pipeline.transcript.videoId")}{" "}
						<span className={styles.videoId}>{transcript.videoId}</span>
					</p>
				</div>
				<Badge variant="info">{transcript.language}</Badge>
			</header>

			<Card className={styles.summary}>
				<p className={styles.summaryLabel}>
					{t("pipeline.transcript.fullText")}
				</p>
				<p className={styles.summaryText}>{transcript.text}</p>
				<p className={styles.summaryMeta}>
					{t("pipeline.transcript.segmentsDuration", {
						count: transcript.segmentCount,
						duration: formatTranscriptTimestamp(transcript.duration),
					})}
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
