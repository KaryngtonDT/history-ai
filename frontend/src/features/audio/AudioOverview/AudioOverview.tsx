import { useEffect, useState } from "react";
import { Link, NavLink, useParams } from "react-router";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { ArtifactJourney } from "@/features/artifacts";
import { PageIntroduction } from "@/features/product";
import {
	AUDIO_PIPELINE_STEPS,
	getAudioPipelineStepLabel,
} from "@/features/product/audioRoutes";
import { useTranslation } from "@/i18n/useTranslation";
import { audioSourceService } from "@/services/audioSource/AudioSourceService";
import type { AudioSource } from "@/services/audioSource/types";
import styles from "./AudioOverview.module.css";

export function AudioOverview() {
	const { t } = useTranslation();
	const { audioId = "" } = useParams();
	const [source, setSource] = useState<AudioSource | null>(null);
	const [loading, setLoading] = useState(true);

	useEffect(() => {
		let cancelled = false;

		audioSourceService
			.getAudioSource(audioId)
			.then((result) => {
				if (!cancelled) {
					setSource(result);
				}
			})
			.finally(() => {
				if (!cancelled) {
					setLoading(false);
				}
			});

		return () => {
			cancelled = true;
		};
	}, [audioId]);

	const title =
		source?.title ??
		t("pipeline.audioOverview.title", { id: audioId.slice(0, 8) });

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow={t("pipeline.audioOverview.eyebrow")}
				title={title}
				description={t("pipeline.audioOverview.description")}
				whatCanIDo={t("pipeline.audioOverview.whatCanIDo")}
			/>

			{loading ? (
				<Spinner label={t("pipeline.audioOverview.loadingSource")} />
			) : null}

			{source ? (
				<Card className={styles.statusCard}>
					<p>
						{t("pipeline.audioOverview.statusLabel")}{" "}
						<strong>{source.status}</strong>
					</p>
					<p className={styles.muted}>{source.originalFilename}</p>
				</Card>
			) : null}

			<nav
				className={styles.tabs}
				aria-label={t("pipeline.audioOverview.sectionsAria")}
			>
				<NavLink
					to={`/audio/${audioId}`}
					end
					className={({ isActive }) =>
						isActive ? `${styles.tab} ${styles.tabActive}` : styles.tab
					}
				>
					{t("pipeline.steps.overview")}
				</NavLink>
				{AUDIO_PIPELINE_STEPS.map((step) => (
					<NavLink
						key={step.id}
						to={step.path(audioId)}
						className={({ isActive }) =>
							isActive ? `${styles.tab} ${styles.tabActive}` : styles.tab
						}
					>
						{getAudioPipelineStepLabel(t, step.id)}
					</NavLink>
				))}
				<Link to="/library" className={styles.tab}>
					{t("pipeline.audioOverview.library")}
				</Link>
			</nav>

			<ArtifactJourney
				videoId={audioId}
				title={t("pipeline.audioOverview.journeyTitle")}
			/>

			<Card className={styles.nextAction}>
				<h2 className={styles.panelTitle}>
					{t("pipeline.audioOverview.nextAction")}
				</h2>
				<Link
					to={`/audio/${audioId}/transcript`}
					className={styles.primaryLink}
					aria-label={t("pipeline.audioOverview.openTranscriptAria")}
				>
					{t("pipeline.audioOverview.openTranscript")}
				</Link>
			</Card>
		</div>
	);
}
