import { useTranslation } from "@/i18n/useTranslation";
import styles from "./PipelineComponents.module.css";

const STATUS_STYLE: Record<string, string> = {
	completed: styles.badgeCompleted,
	running: styles.badgeRunning,
	queued: styles.badgeQueued,
	failed: styles.badgeFailed,
	cancelled: styles.badgeCancelled,
	waiting_user_confirmation: styles.badgeWaiting,
};

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
	const { t } = useTranslation();
	const labelKey = `pipeline.artifactJourney.status${status.replace(/(^|_)(.)/g, (_, _sep, c: string) => c.toUpperCase())}` as const;
	const label = t(labelKey as Parameters<typeof t>[0]) ?? status.replaceAll("_", " ");
	const statusClass = STATUS_STYLE[status] ?? "";
	return (
		<span className={`${styles.badge} ${statusClass}`}>
			{label !== labelKey ? label : status.replaceAll("_", " ")}
		</span>
	);
}

export function StageNotification({
	title,
	message,
	variant = "info",
}: {
	title: string;
	message: string;
	variant?: "info" | "error" | "warning";
}) {
	return (
		<div className={`${styles.notification} ${styles[`notification${variant.charAt(0).toUpperCase()}${variant.slice(1)}`] ?? ""}`}>
			<strong>{title}</strong>
			<p>{message}</p>
		</div>
	);
}

export function StaleArtifactWarning({
	artifactIds,
}: {
	artifactIds: string[];
}) {
	const { t } = useTranslation();

	if (artifactIds.length === 0) {
		return null;
	}

	return (
		<div className={styles.staleWarning}>
			{t("pipeline.progress.staleWarning", { count: String(artifactIds.length) })}
		</div>
	);
}
