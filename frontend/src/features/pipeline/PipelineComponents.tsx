import styles from "./PipelineComponents.module.css";

export function StageProgressBar({
	progressPercent,
	label,
}: {
	progressPercent: number;
	label?: string;
}) {
	return (
		<div className={styles.progressRoot}>
			{label ? <span className={styles.progressLabel}>{label}</span> : null}
			<div className={styles.progressTrack}>
				<div
					className={styles.progressFill}
					style={{ width: `${Math.max(0, Math.min(100, progressPercent))}%` }}
				/>
			</div>
			<span className={styles.progressValue}>{progressPercent}%</span>
		</div>
	);
}

export function StageStatusBadge({ status }: { status: string }) {
	return <span className={styles.badge}>{status.replaceAll("_", " ")}</span>;
}

export function StageNotification({
	title,
	message,
}: {
	title: string;
	message: string;
}) {
	return (
		<div className={styles.notification}>
			<strong>{title}</strong>
			<p>{message}</p>
		</div>
	);
}

export function StaleArtifactWarning({ artifactIds }: { artifactIds: string[] }) {
	if (artifactIds.length === 0) {
		return null;
	}

	return (
		<div className={styles.staleWarning}>
			Later artifacts may be stale after restarting an earlier stage (
			{artifactIds.length} marked).
		</div>
	);
}
