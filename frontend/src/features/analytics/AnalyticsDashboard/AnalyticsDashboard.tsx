import { useTranslation } from "@/i18n";
import type { WorkspaceAnalytics } from "@/services/telemetry/types";
import styles from "./AnalyticsDashboard.module.css";

interface AnalyticsDashboardProps {
	analytics: WorkspaceAnalytics | null;
	loading?: boolean;
	error?: string | null;
}

export function AnalyticsDashboard({
	analytics,
	loading = false,
	error = null,
}: AnalyticsDashboardProps) {
	const { t } = useTranslation();

	if (loading) {
		return (
			<section className={styles.panel}>
				<h2 className={styles.title}>{t("workspace.analytics.title")}</h2>
				<p className={styles.loading}>{t("workspace.analytics.loading")}</p>
			</section>
		);
	}

	if (error) {
		return (
			<section className={styles.panel}>
				<h2 className={styles.title}>{t("workspace.analytics.title")}</h2>
				<p className={styles.error}>{error}</p>
			</section>
		);
	}

	if (!analytics) {
		return null;
	}

	return (
		<section className={styles.panel}>
			<h2 className={styles.title}>{t("workspace.analytics.title")}</h2>
			<div className={styles.analyticsGrid}>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>
						{t("workspace.analytics.labels.processedVideos")}
					</p>
					<p className={styles.statValue}>{analytics.processedVideos}</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>
						{t("workspace.analytics.labels.averageProcessingTime")}
					</p>
					<p className={styles.statValue}>
						{analytics.averageProcessingTimeLabel}
					</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>
						{t("workspace.analytics.labels.averageQuality")}
					</p>
					<p className={styles.statValue}>{analytics.averageQuality}</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>
						{t("workspace.analytics.labels.successRate")}
					</p>
					<p className={styles.statValue}>{analytics.successRate}%</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>
						{t("workspace.analytics.labels.gpuUsage")}
					</p>
					<p className={styles.statValue}>{analytics.gpuUsagePercent}%</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>
						{t("workspace.analytics.labels.topTranslationProvider")}
					</p>
					<p className={styles.statValue}>{analytics.topTranslationProvider}</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>
						{t("workspace.analytics.labels.topTtsProvider")}
					</p>
					<p className={styles.statValue}>{analytics.topTtsProvider}</p>
				</article>
			</div>
		</section>
	);
}
