import { useTranslation } from "@/i18n";
import type { BatchJobStatus } from "@/services/workspace/types";
import styles from "./BatchProgress.module.css";

interface BatchProgressProps {
	progress: number;
	status: BatchJobStatus | null;
	loading?: boolean;
}

export function BatchProgress({
	progress,
	status,
	loading = false,
}: BatchProgressProps) {
	const { t } = useTranslation();

	const formatStatus = (value: BatchJobStatus | null): string => {
		if (value === null) {
			return t("workspace.batch.status.idle");
		}

		return t(`workspace.batch.status.${value}`);
	};

	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>{t("workspace.batch.starting")}</p>
			</div>
		);
	}

	if (status === null) {
		return null;
	}

	const clampedProgress = Math.max(0, Math.min(100, progress));

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>{t("workspace.batch.overallProgress")}</p>
				<span className={styles.status}>{formatStatus(status)}</span>
			</div>
			<div
				className={styles.barTrack}
				role="progressbar"
				aria-valuenow={clampedProgress}
				aria-valuemin={0}
				aria-valuemax={100}
				aria-label={t("workspace.batch.progressAria")}
			>
				<div
					className={styles.barFill}
					style={{ width: `${clampedProgress}%` }}
				/>
			</div>
			<p className={styles.percentage}>{clampedProgress}%</p>
		</div>
	);
}
