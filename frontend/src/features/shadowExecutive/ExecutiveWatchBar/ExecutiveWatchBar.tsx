import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowExecutiveService } from "@/services/shadowExecutive/ShadowExecutiveService";
import type { ExecutiveDashboard } from "@/services/shadowExecutive/types";
import styles from "./ExecutiveWatchBar.module.css";

const POLL_INTERVAL_MS = 5000;

function priorityClass(priority: string | null): string {
	switch (priority) {
		case "critical":
			return styles.priorityCritical;
		case "high":
			return styles.priorityHigh;
		case "low":
			return styles.priorityLow;
		default:
			return styles.priorityNormal;
	}
}

export function ExecutiveWatchBar() {
	const { t } = useTranslation();
	const [dashboard, setDashboard] = useState<ExecutiveDashboard | null>(null);
	const [refreshing, setRefreshing] = useState(false);

	useEffect(() => {
		let cancelled = false;

		async function refresh() {
			setRefreshing(true);

			try {
				const next = await shadowExecutiveService.getDashboard();
				if (!cancelled) {
					setDashboard(next);
				}
			} finally {
				if (!cancelled) {
					setRefreshing(false);
				}
			}
		}

		void refresh();
		const intervalId = window.setInterval(() => {
			void refresh();
		}, POLL_INTERVAL_MS);

		return () => {
			cancelled = true;
			window.clearInterval(intervalId);
		};
	}, []);

	const watch = dashboard?.watch;

	return (
		<section className={styles.panel}>
			<header className={styles.header}>
				<h3>{t("shadowExecutive.watchBar.title")}</h3>
				{refreshing ? (
					<span className={styles.refreshing}>{t("common.loading")}</span>
				) : null}
			</header>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowExecutive.watchBar.objective")}
				</span>
				<span className={styles.value}>
					{watch?.objective ?? t("shadowExecutive.empty.objective")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowExecutive.watchBar.priority")}
				</span>
				<span
					className={`${styles.value} ${priorityClass(watch?.priority ?? null)}`}
				>
					{watch?.priority
						? t("shadowExecutive.priorityLabel", { value: watch.priority })
						: t("shadowExecutive.empty.priority")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowExecutive.watchBar.mission")}
				</span>
				<span className={styles.value}>
					{watch?.mission ?? t("shadowExecutive.empty.mission")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowExecutive.watchBar.recommendedPause")}
				</span>
				<span className={styles.value}>
					{watch?.recommendedPause ?? t("shadowExecutive.empty.pause")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowExecutive.watchBar.recommendedReview")}
				</span>
				<span className={styles.value}>
					{watch?.recommendedReview ?? t("shadowExecutive.empty.review")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowExecutive.watchBar.nextTopic")}
				</span>
				<span className={styles.value}>
					{watch?.nextTopic ?? t("shadowExecutive.empty.nextTopic")}
				</span>
			</div>
		</section>
	);
}
