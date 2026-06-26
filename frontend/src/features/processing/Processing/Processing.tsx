import { useEffect, useState } from "react";
import { useParams } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { isTerminalProcessingStatus } from "@/services/processing/ProcessingMonitor";
import { processingService } from "@/services/processing/ProcessingService";
import type { ProcessingData } from "@/services/processing/types";
import { ProcessingArtifacts } from "../ProcessingArtifacts";
import { ProcessingHeader } from "../ProcessingHeader";
import { ProcessingStatus } from "../ProcessingStatus";
import { ProcessingSummary } from "../ProcessingSummary";
import { ProcessingTimeline } from "../ProcessingTimeline";
import styles from "./Processing.module.css";

export function Processing() {
	const { id } = useParams();
	const [data, setData] = useState<ProcessingData | null>(null);
	const [loading, setLoading] = useState(true);
	const [notFound, setNotFound] = useState(false);
	const [loadError, setLoadError] = useState<string | null>(null);

	useEffect(() => {
		if (!id) {
			setLoading(false);
			setNotFound(true);
			return;
		}

		let cancelled = false;
		let unsubscribe: (() => void) | undefined;

		void processingService
			.getProcessing(id)
			.then((initial) => {
				if (cancelled) {
					return;
				}

				if (!initial) {
					setNotFound(true);
					setData(null);
					setLoadError(null);
					setLoading(false);
					return;
				}

				setData(initial);
				setNotFound(false);
				setLoadError(null);
				setLoading(false);

				if (!isTerminalProcessingStatus(initial.status)) {
					unsubscribe = processingService.subscribeToProcessing(
						id,
						(update) => {
							if (!cancelled) {
								setData(update);
							}
						},
						() => {
							if (!cancelled) {
								setLoadError(
									"Could not reach the server. Check that the backend is running.",
								);
								setData(null);
								setNotFound(false);
							}
						},
					);
				}
			})
			.catch(() => {
				if (!cancelled) {
					setLoadError(
						"Could not reach the server. Check that the backend is running.",
					);
					setData(null);
					setNotFound(false);
					setLoading(false);
				}
			});

		return () => {
			cancelled = true;
			unsubscribe?.();
		};
	}, [id]);

	if (loading) {
		return (
			<div className={styles.loading}>
				<Spinner label="Loading processing status" />
			</div>
		);
	}

	if (notFound) {
		return (
			<EmptyState
				title="Processing job not found"
				description="Return to the dashboard to continue."
			/>
		);
	}

	if (loadError !== null) {
		return (
			<EmptyState title="Unable to load processing" description={loadError} />
		);
	}

	if (!data) {
		return null;
	}

	return (
		<div className={styles.root}>
			<ProcessingHeader title={data.title} />
			<div className={styles.content}>
				<ProcessingStatus data={data} />
				<ProcessingTimeline steps={data.steps} />
				{data.status === "completed" ? (
					<>
						<ProcessingSummary title={data.title} />
						<ProcessingArtifacts contentId={data.contentId} />
					</>
				) : null}
			</div>
		</div>
	);
}
