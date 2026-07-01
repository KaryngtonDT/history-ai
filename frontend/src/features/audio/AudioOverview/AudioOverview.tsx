import { useEffect, useState } from "react";
import { Link, NavLink, useParams } from "react-router";
import { Card } from "@/components/ui/Card";
import { Spinner } from "@/components/ui/Spinner";
import { ArtifactJourney } from "@/features/artifacts";
import { PageIntroduction } from "@/features/product";
import { AUDIO_PIPELINE_STEPS } from "@/features/product/audioRoutes";
import { audioSourceService } from "@/services/audioSource/AudioSourceService";
import type { AudioSource } from "@/services/audioSource/types";
import styles from "./AudioOverview.module.css";

export function AudioOverview() {
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

	const title = source?.title ?? `Audio ${audioId.slice(0, 8)}…`;

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow="Audio"
				title={title}
				description="Central hub for this audio source — transcript, translation, and knowledge outputs."
				whatCanIDo="Open any step below or continue processing from Home."
			/>

			{loading ? <Spinner label="Loading audio source" /> : null}

			{source ? (
				<Card className={styles.statusCard}>
					<p>
						Status: <strong>{source.status}</strong>
					</p>
					<p className={styles.muted}>{source.originalFilename}</p>
				</Card>
			) : null}

			<nav className={styles.tabs} aria-label="Audio sections">
				<NavLink
					to={`/audio/${audioId}`}
					end
					className={({ isActive }) =>
						isActive ? `${styles.tab} ${styles.tabActive}` : styles.tab
					}
				>
					Overview
				</NavLink>
				{AUDIO_PIPELINE_STEPS.map((step) => (
					<NavLink
						key={step.id}
						to={step.path(audioId)}
						className={({ isActive }) =>
							isActive ? `${styles.tab} ${styles.tabActive}` : styles.tab
						}
					>
						{step.label}
					</NavLink>
				))}
				<Link to="/library" className={styles.tab}>
					Library
				</Link>
			</nav>

			<ArtifactJourney videoId={audioId} title="Processing progress" />

			<Card className={styles.nextAction}>
				<h2 className={styles.panelTitle}>Next action</h2>
				<Link
					to={`/audio/${audioId}/transcript`}
					className={styles.primaryLink}
					aria-label="Open transcript"
				>
					Open transcript →
				</Link>
			</Card>
		</div>
	);
}
