import { useEffect, useState } from "react";
import { Link, NavLink, useParams } from "react-router";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { ArtifactJourney } from "@/features/artifacts";
import { ExplainThisButton } from "@/features/help";
import { PipelineProgressPanel } from "@/features/pipeline";
import { PageIntroduction } from "@/features/product";
import {
	getVideoPipelineStepLabel,
	videoPipelinePath,
} from "@/features/product/videoRoutes";
import { useTranslation } from "@/i18n/useTranslation";
import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import { qualityService } from "@/services/quality/QualityService";
import type { QualityReport } from "@/services/quality/types";
import styles from "./VideoOverview.module.css";

const OVERVIEW_TABS = [
	{ id: "overview", path: (id: string) => `/video/${id}` },
	{
		id: "transcript",
		path: (id: string) => videoPipelinePath("transcript", id),
	},
	{
		id: "translations",
		path: (id: string) => videoPipelinePath("translations", id),
	},
	{
		id: "audio",
		path: (id: string) => videoPipelinePath("audio", id),
	},
	{
		id: "voice-clone",
		path: (id: string) => videoPipelinePath("voice-clone", id),
	},
	{
		id: "lip-sync",
		path: (id: string) => videoPipelinePath("lip-sync", id),
	},
	{
		id: "render",
		path: (id: string) => videoPipelinePath("render", id),
	},
	{
		id: "shadow",
		path: (id: string) => `/video/${id}/watch`,
	},
] as const;

export function VideoOverview() {
	const { t } = useTranslation();
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
				eyebrow={t("pipeline.videoOverview.eyebrow")}
				title={t("pipeline.videoOverview.title", { id: videoId.slice(0, 8) })}
				description={t("pipeline.videoOverview.description")}
				whatCanIDo={t("pipeline.videoOverview.whatCanIDo")}
				secondaryActions={<ExplainThisButton featureId="video-upload" />}
			/>

			<nav
				className={styles.tabs}
				aria-label={t("pipeline.videoOverview.sectionsAria")}
			>
				{OVERVIEW_TABS.map((tab) => (
					<NavLink
						key={tab.id}
						to={tab.path(videoId)}
						end={tab.id === "overview"}
						className={({ isActive }) =>
							isActive ? `${styles.tab} ${styles.tabActive}` : styles.tab
						}
					>
						{tab.id === "overview"
							? t("pipeline.steps.overview")
							: tab.id === "shadow"
								? t("pipeline.steps.shadow")
								: getVideoPipelineStepLabel(
										t,
										tab.id as
											| "transcript"
											| "translations"
											| "audio"
											| "voice-clone"
											| "lip-sync"
											| "render",
									)}
					</NavLink>
				))}
				<Link to="/workspace" className={styles.tab}>
					{t("pipeline.videoOverview.analytics")}
				</Link>
			</nav>

			<ArtifactJourney
				videoId={videoId}
				title={t("pipeline.videoOverview.journeyTitle")}
			/>

			{videoId ? <PipelineProgressPanel sourceId={videoId} /> : null}

			<div className={styles.grid}>
				<Card className={styles.panel}>
					<h2 className={styles.panelTitle}>
						{t("pipeline.videoOverview.aiDirector")}
					</h2>
					{loading ? (
						<Spinner
							label={t("pipeline.videoOverview.loadingRecommendation")}
						/>
					) : recommendation ? (
						<>
							<p className={styles.panelBody}>
								{t("pipeline.videoOverview.strategy")}{" "}
								<strong>{recommendation.strategy}</strong>
							</p>
							<p className={styles.panelMuted}>{recommendation.explanation}</p>
						</>
					) : (
						<p className={styles.panelMuted}>
							{t("pipeline.videoOverview.noRecommendation")}
						</p>
					)}
				</Card>

				<Card className={styles.panel}>
					<h2 className={styles.panelTitle}>
						{t("pipeline.videoOverview.quality")}
					</h2>
					{loading ? (
						<Spinner label={t("pipeline.videoOverview.loadingQuality")} />
					) : qualityReport ? (
						<p className={styles.panelBody}>
							{t("pipeline.videoOverview.score")}{" "}
							<strong>{qualityReport.overallScore}</strong> / 100
						</p>
					) : (
						<p className={styles.panelMuted}>
							{t("pipeline.videoOverview.qualityUnavailable")}
						</p>
					)}
					<Link
						to={videoPipelinePath("render", videoId)}
						className={styles.inlineLink}
					>
						{t("pipeline.videoOverview.openFinalVideo")}
					</Link>
				</Card>
			</div>

			<div className={styles.actions}>
				<Link
					to={videoPipelinePath("transcript", videoId)}
					className={styles.primaryLink}
				>
					{t("pipeline.videoOverview.nextOpenTranscript")}
				</Link>
				<Link to="/workspace" className={styles.secondaryLink}>
					{t("pipeline.videoOverview.viewWorkspaceAnalytics")}
				</Link>
				<Link to={`/video/${videoId}/watch`} className={styles.secondaryLink}>
					{t("pipeline.steps.shadow")}
				</Link>
			</div>
		</div>
	);
}
