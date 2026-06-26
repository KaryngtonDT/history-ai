import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import { Spinner } from "@/components/ui/Spinner";
import type { ProcessingData } from "@/services/processing/types";
import styles from "./ProcessingStatus.module.css";

interface ProcessingStatusProps {
	data: ProcessingData;
}

function statusBadgeVariant(
	status: ProcessingData["status"],
): "neutral" | "info" | "success" {
	if (status === "completed") {
		return "success";
	}
	if (status === "running") {
		return "info";
	}
	return "neutral";
}

function statusLabel(status: ProcessingData["status"]): string {
	if (status === "completed") {
		return "Completed";
	}
	if (status === "running") {
		return "Running";
	}
	return "Pending";
}

export function ProcessingStatus({ data }: ProcessingStatusProps) {
	const isRunning = data.status === "running";

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>Progress</p>
				<Badge variant={statusBadgeVariant(data.status)}>
					{statusLabel(data.status)}
				</Badge>
			</div>
			<Progress value={data.progress} />
			<p className={styles.percent}>{data.progress} %</p>
			<div className={styles.currentStep}>
				<p className={styles.currentLabel}>Current step</p>
				<div className={styles.currentValue}>
					{isRunning ? <Spinner label="Processing step" /> : null}
					<p className={styles.currentText}>{data.currentStep}</p>
				</div>
			</div>
		</Card>
	);
}
