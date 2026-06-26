import { useEffect, useState } from "react";
import { Card } from "@/components/ui/Card";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { artifactService } from "@/services/artifact/ArtifactService";
import type { Artifact } from "@/services/artifact/types";
import styles from "./ProcessingArtifacts.module.css";

interface ProcessingArtifactsProps {
	contentId: string;
}

export function ProcessingArtifacts({ contentId }: ProcessingArtifactsProps) {
	const [summary, setSummary] = useState<Artifact | null>(null);
	const [loading, setLoading] = useState(true);
	const [loadError, setLoadError] = useState<string | null>(null);
	const [isEmpty, setIsEmpty] = useState(false);

	useEffect(() => {
		let cancelled = false;

		setLoading(true);
		setLoadError(null);
		setIsEmpty(false);
		setSummary(null);

		void artifactService
			.getSummaryArtifact(contentId)
			.then((artifact) => {
				if (cancelled) {
					return;
				}

				if (!artifact) {
					setIsEmpty(true);
					setSummary(null);
					return;
				}

				setSummary(artifact);
				setIsEmpty(false);
			})
			.catch(() => {
				if (!cancelled) {
					setLoadError(
						"Could not load artifacts. Check that the backend is running.",
					);
					setSummary(null);
					setIsEmpty(false);
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
	}, [contentId]);

	if (loading) {
		return (
			<div className={styles.loading}>
				<Spinner label="Loading artifacts" />
			</div>
		);
	}

	if (loadError !== null) {
		return (
			<EmptyState title="Unable to load artifacts" description={loadError} />
		);
	}

	if (isEmpty || !summary) {
		return (
			<EmptyState
				title="No artifacts yet"
				description="Generated learning artifacts will appear here once processing output is available."
			/>
		);
	}

	return (
		<Card className={styles.card}>
			<p className={styles.label}>Summary</p>
			<p className={styles.content}>{summary.content}</p>
		</Card>
	);
}
