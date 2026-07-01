import { Link } from "react-router";
import { useTranslation } from "@/i18n/useTranslation";
import { buildArtifactJourney } from "../journeyModel";
import styles from "./ArtifactJourney.module.css";

interface ArtifactJourneyProps {
	videoId: string | null;
	title?: string;
}

function statusClass(status: string): string {
	const base = styles.badge;

	if (status === "open") {
		return `${base} ${styles.badgeOpen}`;
	}

	if (status === "generate") {
		return `${base} ${styles.badgeGenerate}`;
	}

	return `${base} ${styles.badgeLocked}`;
}

function actionLabel(
	status: string,
	t: (key: string, params?: Record<string, string | number>) => string,
): string {
	if (status === "open") {
		return t("pipeline.artifactJourney.actionOpen");
	}

	if (status === "generate") {
		return t("pipeline.artifactJourney.actionStart");
	}

	return t("pipeline.artifactJourney.actionLocked");
}

export function ArtifactJourney({ videoId, title }: ArtifactJourneyProps) {
	const { t } = useTranslation();
	const resolvedTitle = title ?? t("pipeline.artifactJourney.defaultTitle");
	const steps = buildArtifactJourney(videoId, t);

	return (
		<section className={styles.root} aria-label={resolvedTitle}>
			<h2 className={styles.title}>{resolvedTitle}</h2>
			<p className={styles.subtitle}>
				{t("pipeline.artifactJourney.subtitle")}
			</p>
			<div className={styles.track}>
				{steps.map((step, index) => (
					<div key={step.id} style={{ display: "contents" }}>
						<article className={styles.card}>
							<div className={styles.cardHeader}>
								<h3 className={styles.cardTitle}>{step.label}</h3>
								<span className={statusClass(step.status)}>
									{t(
										`pipeline.artifactJourney.status${step.status.charAt(0).toUpperCase()}${step.status.slice(1)}`,
									)}
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
