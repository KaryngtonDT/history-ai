import { Link } from "react-router";
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

function actionLabel(status: string): string {
	if (status === "open") {
		return "Open";
	}

	if (status === "generate") {
		return "Start";
	}

	return "Locked";
}

export function ArtifactJourney({
	videoId,
	title = "Artifact journey",
}: ArtifactJourneyProps) {
	const steps = buildArtifactJourney(videoId);

	return (
		<section className={styles.root} aria-label={title}>
			<h2 className={styles.title}>{title}</h2>
			<p className={styles.subtitle}>
				Follow the pipeline from video upload to final quality report.
			</p>
			<div className={styles.track}>
				{steps.map((step, index) => (
					<div key={step.id} style={{ display: "contents" }}>
						<article className={styles.card}>
							<div className={styles.cardHeader}>
								<h3 className={styles.cardTitle}>{step.label}</h3>
								<span className={statusClass(step.status)}>{step.status}</span>
							</div>
							<p className={styles.cardDescription}>{step.description}</p>
							{step.dependsOnLabel ? (
								<p className={styles.dependency}>
									Depends on: {step.dependsOnLabel}
								</p>
							) : null}
							{step.path && step.status !== "locked" ? (
								<Link to={step.path} className={styles.action}>
									{actionLabel(step.status)} →
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
