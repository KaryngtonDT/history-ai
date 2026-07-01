import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n";
import type { ProcessingData } from "@/services/processing/types";
import styles from "./ProcessingStatus.module.css";

interface ProcessingStatusProps {
	data: ProcessingData;
}

function statusBadgeVariant(
	status: ProcessingData["status"],
): "neutral" | "info" | "success" | "danger" {
	if (status === "completed") {
		return "success";
	}
	if (status === "running") {
		return "info";
	}
	if (status === "failed") {
		return "danger";
	}
	return "neutral";
}

function statusKey(status: ProcessingData["status"]): string {
	if (status === "completed") {
		return "workspace.processing.status.completed";
	}
	if (status === "running") {
		return "workspace.processing.status.running";
	}
	if (status === "failed") {
		return "workspace.processing.status.failed";
	}
	if (status === "cancelled") {
		return "workspace.processing.status.cancelled";
	}
	return "workspace.processing.status.pending";
}

export function ProcessingStatus({ data }: ProcessingStatusProps) {
	const { t } = useTranslation();
	const isRunning = data.status === "running";

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>{t("workspace.processing.progress")}</p>
				<Badge variant={statusBadgeVariant(data.status)}>
					{t(statusKey(data.status))}
				</Badge>
			</div>
			<Progress value={data.progress} />
			<p className={styles.percent}>{data.progress} %</p>
			<div className={styles.currentStep}>
				<p className={styles.currentLabel}>
					{t("workspace.processing.currentStep")}
				</p>
				<div className={styles.currentValue}>
					{isRunning ? (
						<Spinner label={t("workspace.processing.processingStep")} />
					) : null}
					<p className={styles.currentText}>{data.currentStep}</p>
				</div>
			</div>
		</Card>
	);
}
