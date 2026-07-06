import { Link } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n/useTranslation";
import { type ArtifactStatus, buildArtifactJourney } from "../journeyModel";
import { useVideoPipelineProgress } from "../useVideoPipelineProgress";
import styles from "./ArtifactJourney.module.css";

interface ArtifactJourneyProps {
	videoId: string | null;
	title?: string;
}

function statusClass(status: ArtifactStatus): string {
	const base = styles.badge;

	switch (status) {
		case "completed":
			return `${base} ${styles.badgeCompleted}`;
		case "in_progress":
			return `${base} ${styles.badgeInProgress}`;
		case "failed":
			return `${base} ${styles.badgeFailed}`;
		case "open":
			return `${base} ${styles.badgeOpen}`;
		case "generate":
			return `${base} ${styles.badgeGenerate}`;
		default:
			return `${base} ${styles.badgeLocked}`;
	}
}

function actionLabel(
	status: ArtifactStatus,
	t: (key: string, params?: Record<string, string | number>) => string,
): string {
	if (status === "completed") {
		return t("pipeline.artifactJourney.actionView");
	}

	if (status === "open" || status === "in_progress" || status === "failed") {
		return t("pipeline.artifactJourney.actionOpen");
	}

	if (status === "generate") {
		return t("pipeline.artifactJourney.actionStart");
	}

	return t("pipeline.artifactJourney.actionLocked");
}

function statusKey(status: ArtifactStatus): string {
	return `pipeline.artifactJourney.status${status
		.split("_")
		.map((part) => part.charAt(0).toUpperCase() + part.slice(1))
		.join("")}`;
}

export function ArtifactJourney({ videoId, title }: ArtifactJourneyProps) {
	const { t } = useTranslation();
	const progress = useVideoPipelineProgress(videoId);
	const resolvedTitle = title ?? t("pipeline.artifactJourney.defaultTitle");
	const steps = buildArtifactJourney(videoId, t, progress);

	return (
		<section className={styles.root} aria-label={resolvedTitle}>
			<h2 className={styles.title}>{resolvedTitle}</h2>
			<p className={styles.subtitle}>
				{t("pipeline.artifactJourney.subtitle")}
			</p>
			{videoId && progress.loading ? (
				<div className={styles.loading}>
					<Spinner label={t("pipeline.artifactJourney.loadingProgress")} />
				</div>
			) : null}
			{!videoId ? (
				<EmptyState
					title={t("pipeline.artifactJourney.noVideoTitle")}
					description={t("pipeline.artifactJourney.noVideoDescription")}
					action={
						<Link to="/video/upload" className={styles.action}>
							{t("pipeline.artifactJourney.noVideoAction")} →
						</Link>
					}
				/>
			) : null}
			<div className={styles.track}>
				{steps.map((step, index) => (
					<div key={step.id} style={{ display: "contents" }}>
						<article className={styles.card}>
							<div className={styles.cardHeader}>
								<h3 className={styles.cardTitle}>{step.label}</h3>
								<span className={statusClass(step.status)}>
									{t(statusKey(step.status))}
								</span>
							</div>
							<p className={styles.cardDescription}>{step.description}</p>
							{step.dependsOnLabel ? (
								<p className={styles.dependency}>
									{t("pipeline.artifactJourney.dependsOn", {
										step: step.dependsOnLabel,
									})}
								</p>
							) : null}
							{step.path && step.status !== "locked" ? (
								<Link to={step.path} className={styles.action}>
									{actionLabel(step.status, t)} →
								</Link>
							) : null}
							{step.status === "locked" && step.dependsOnLabel ? (
								<p className={styles.lockedHint}>
									{t("pipeline.artifactJourney.lockedHint", {
										step: step.dependsOnLabel,
									})}
								</p>
							) : null}
						</article>
						{index < steps.length - 1 ? (
							<span className={styles.arrow} aria-hidden="true">
								↓
							</span>
						) : null}
					</div>
				))}
			</div>
		</section>
	);
}
