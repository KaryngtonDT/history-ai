import { useEffect, useState } from "react";
import { useParams } from "react-router";
import { EmptyState } from "@/components/ui/EmptyState";
import { processingService } from "@/services/processing/ProcessingService";
import type { ProcessingData } from "@/services/processing/types";
import { ProcessingHeader } from "../ProcessingHeader";
import { ProcessingStatus } from "../ProcessingStatus";
import { ProcessingSummary } from "../ProcessingSummary";
import { ProcessingTimeline } from "../ProcessingTimeline";
import styles from "./Processing.module.css";

export function Processing() {
	const { id } = useParams();
	const [data, setData] = useState<ProcessingData | null>(null);
	const [notFound, setNotFound] = useState(false);

	useEffect(() => {
		if (!id) {
			setNotFound(true);
			return;
		}

		const initial = processingService.getProcessing(id);
		if (!initial) {
			setNotFound(true);
			return;
		}

		setData(initial);
		setNotFound(false);

		let cancelled = false;

		void processingService.simulateProcessing(id, {
			onUpdate: (update) => {
				if (!cancelled) {
					setData(update);
				}
			},
		});

		return () => {
			cancelled = true;
		};
	}, [id]);

	if (notFound) {
		return (
			<EmptyState
				title="Processing job not found"
				description="Return to the dashboard to continue."
			/>
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
					<ProcessingSummary title={data.title} />
				) : null}
			</div>
		</div>
	);
}
