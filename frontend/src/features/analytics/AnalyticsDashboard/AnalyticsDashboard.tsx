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
	if (loading) {
		return (
			<section className={styles.panel}>
				<h2 className={styles.title}>Workspace Analytics</h2>
				<p className={styles.loading}>Loading analytics...</p>
			</section>
		);
	}

	if (error) {
		return (
			<section className={styles.panel}>
				<h2 className={styles.title}>Workspace Analytics</h2>
				<p className={styles.error}>{error}</p>
			</section>
		);
	}

	if (!analytics) {
		return null;
	}

	return (
		<section className={styles.panel}>
			<h2 className={styles.title}>Workspace Analytics</h2>
			<div className={styles.analyticsGrid}>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>Processed Videos</p>
					<p className={styles.statValue}>{analytics.processedVideos}</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>Average Processing Time</p>
					<p className={styles.statValue}>
						{analytics.averageProcessingTimeLabel}
					</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>Average Quality</p>
					<p className={styles.statValue}>{analytics.averageQuality}</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>Success Rate</p>
					<p className={styles.statValue}>{analytics.successRate}%</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>GPU Usage</p>
					<p className={styles.statValue}>{analytics.gpuUsagePercent}%</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>Top Translation Provider</p>
					<p className={styles.statValue}>{analytics.topTranslationProvider}</p>
				</article>
				<article className={styles.statCard}>
					<p className={styles.statLabel}>Top TTS Provider</p>
					<p className={styles.statValue}>{analytics.topTtsProvider}</p>
				</article>
			</div>
		</section>
	);
}
