import { useEffect, useState } from "react";
import { Link, NavLink, useParams } from "react-router";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { ArtifactJourney } from "@/features/artifacts";
import { ExplainThisButton } from "@/features/help";
import { PageIntroduction } from "@/features/product";
import { videoPipelinePath } from "@/features/product/videoRoutes";
import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import { qualityService } from "@/services/quality/QualityService";
import type { QualityReport } from "@/services/quality/types";
import styles from "./VideoOverview.module.css";

const OVERVIEW_TABS = [
	{ id: "overview", label: "Overview", path: (id: string) => `/video/${id}` },
	{
		id: "transcript",
		label: "Transcript",
		path: (id: string) => videoPipelinePath("transcript", id),
	},
	{
		id: "translations",
		label: "Translations",
		path: (id: string) => videoPipelinePath("translations", id),
	},
	{
		id: "audio",
		label: "Audio",
		path: (id: string) => videoPipelinePath("audio", id),
	},
	{
		id: "voice-clone",
		label: "Cloned Voice",
		path: (id: string) => videoPipelinePath("voice-clone", id),
	},
	{
		id: "lip-sync",
		label: "Lip Sync Preview",
		path: (id: string) => videoPipelinePath("lip-sync", id),
	},
	{
		id: "render",
		label: "Final Video",
		path: (id: string) => videoPipelinePath("render", id),
	},
] as const;

export function VideoOverview() {
	const { videoId = "" } = useParams();
	const [recommendation, setRecommendation] =
		useState<PipelineRecommendation | null>(null);
	const [qualityReport, setQualityReport] = useState<QualityReport | null>(
		null,
	);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		void Promise.all([
			orchestratorService.loadRecommendation(),
			qualityService.loadPreviewQuality(),
		])
			.then(([rec, quality]) => {
				setRecommendation(rec);
				setQualityReport(quality);
			})
			.catch(() => {
				setRecommendation(null);
				setQualityReport(null);
			})
			.finally(() => setLoading(false));
	}, []);

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow="Video"
				title={`Video ${videoId.slice(0, 8)}…`}
				description="Central hub for this video's localization pipeline."
				whatCanIDo="Review pipeline progress, open any step, or continue to the next action."
				secondaryActions={<ExplainThisButton featureId="video-upload" />}
			/>

			<nav className={styles.tabs} aria-label="Video sections">
				{OVERVIEW_TABS.map((tab) => (
					<NavLink
						key={tab.id}
						to={tab.path(videoId)}
						end={tab.id === "overview"}
						className={({ isActive }) =>
							isActive ? `${styles.tab} ${styles.tabActive}` : styles.tab
						}
					>
						{tab.label}
					</NavLink>
				))}
				<Link to="/workspace" className={styles.tab}>
					Analytics
				</Link>
			</nav>

			<ArtifactJourney videoId={videoId} title="Pipeline progress" />

			<div className={styles.grid}>
				<Card className={styles.panel}>
					<h2 className={styles.panelTitle}>AI Director</h2>
					{loading ? (
						<Spinner label="Loading recommendation" />
					) : recommendation ? (
						<>
							<p className={styles.panelBody}>
								Strategy: <strong>{recommendation.strategy}</strong>
							</p>
							<p className={styles.panelMuted}>{recommendation.explanation}</p>
						</>
					) : (
						<p className={styles.panelMuted}>
							No recommendation available. Upload with automatic mode.
						</p>
					)}
				</Card>

				<Card className={styles.panel}>
					<h2 className={styles.panelTitle}>Quality</h2>
					{loading ? (
						<Spinner label="Loading quality" />
					) : qualityReport ? (
						<p className={styles.panelBody}>
							Score: <strong>{qualityReport.overallScore}</strong> / 100
						</p>
					) : (
						<p className={styles.panelMuted}>
							Quality report not available yet.
						</p>
					)}
					<Link
						to={videoPipelinePath("render", videoId)}
						className={styles.inlineLink}
					>
						Open final video →
					</Link>
				</Card>
			</div>

			<div className={styles.actions}>
				<Link
					to={videoPipelinePath("transcript", videoId)}
					className={styles.primaryLink}
				>
					Next: Open transcript →
				</Link>
				<Link to="/workspace" className={styles.secondaryLink}>
					View workspace analytics →
				</Link>
			</div>
		</div>
	);
}
