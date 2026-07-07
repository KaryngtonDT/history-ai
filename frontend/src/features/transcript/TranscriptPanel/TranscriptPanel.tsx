import { useEffect, useState } from "react";
import { Link, useParams } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { PipelineProgressPanel } from "@/features/pipeline";
import { isPipelineWaitingForTranscriptChoice } from "@/features/pipeline/pipelineChoiceUtils";
import { usePipelineChoiceState } from "@/features/pipeline/usePipelineChoiceState";
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
	const { isWaitingForChoice, loading: pipelineLoading, status: pipelineStatus } =
		usePipelineChoiceState(videoId || null);

	useEffect(() => {
		let cancelled = false;

		setLoading(true);
		setTranscript(null);
		setActiveIndex(0);

		if (
			!resourceId ||
			pipelineLoading ||
			pipelineStatus === null ||
			isPipelineWaitingForTranscriptChoice(pipelineStatus)
		) {
			setLoading(false);
			return () => {
				cancelled = true;
			};
		}

		transcriptService.getTranscript(resourceId).then((result) => {
			if (!cancelled) {
				setTranscript(result);
				setLoading(false);
			}
		});

		return () => {
			cancelled = true;
		};
	}, [pipelineLoading, pipelineStatus, resourceId]);

	if (videoId) {
		return (
			<div className={styles.root}>
				<PipelineProgressPanel sourceId={videoId} />
				{loading ? (
					<Spinner label={t("pipeline.transcript.loading")} />
				) : !transcript && isWaitingForChoice ? (
					<EmptyState
						title={t("pipeline.progress.youtubeChoiceTitle")}
						description={t("pipeline.progress.youtubeChoiceDescription")}
					/>
				) : !transcript ? (
					<EmptyState
						title={t("pipeline.transcript.unavailableTitle")}
						description={t("pipeline.transcript.unavailableDescription")}
						action={
							<Link to="/video/upload">
								{t("pipeline.transcript.emptyAction")} →
							</Link>
						}
					/>
				) : (
					<TranscriptContent
						transcript={transcript}
						activeIndex={activeIndex}
						onSegmentSelect={setActiveIndex}
					/>
				)}
			</div>
		);
	}

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
					action={
						<Link to="/video/upload">
							{t("pipeline.transcript.emptyAction")} →
						</Link>
					}
				/>
			</div>
		);
	}

	return (
		<TranscriptContent
			transcript={transcript}
			activeIndex={activeIndex}
			onSegmentSelect={setActiveIndex}
		/>
	);
}

function TranscriptContent({
	transcript,
	activeIndex,
	onSegmentSelect,
}: {
	transcript: VideoTranscript;
	activeIndex: number;
	onSegmentSelect: (index: number) => void;
}) {
	const { t } = useTranslation();
	const isDeterministicPlaceholder = transcript.text.includes(
		"Deterministic transcript",
	);

	return (
		<>
			{isDeterministicPlaceholder ? (
				<p className={styles.devNotice}>
					{t("pipeline.transcript.deterministicNotice")}
				</p>
			) : null}
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
				onSegmentSelect={onSegmentSelect}
			/>
		</>
	);
}
