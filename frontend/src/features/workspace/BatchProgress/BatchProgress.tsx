import type { BatchJobStatus } from "@/services/workspace/types";
import { workspaceService } from "@/services/workspace/WorkspaceService";
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
	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>Starting batch processing...</p>
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
				<p className={styles.title}>Overall Progress</p>
				<span className={styles.status}>
					{workspaceService.formatBatchStatus(status)}
				</span>
			</div>
			<div
				className={styles.barTrack}
				role="progressbar"
				aria-valuenow={clampedProgress}
				aria-valuemin={0}
				aria-valuemax={100}
				aria-label="Batch progress"
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
