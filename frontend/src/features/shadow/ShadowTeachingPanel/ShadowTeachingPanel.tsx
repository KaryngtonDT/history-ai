import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowTeachingService } from "@/services/shadowTeaching/ShadowTeachingService";
import type { TeachingCurrentResponse } from "@/services/shadowTeaching/types";
import styles from "./ShadowTeachingPanel.module.css";

const POLL_INTERVAL_MS = 5000;

export function ShadowTeachingPanel() {
	const { t } = useTranslation();
	const [current, setCurrent] = useState<TeachingCurrentResponse | null>(null);
	const [refreshing, setRefreshing] = useState(false);

	useEffect(() => {
		let cancelled = false;

		async function refresh() {
			setRefreshing(true);

			try {
				const next = await shadowTeachingService.getCurrent();
				if (!cancelled) {
					setCurrent(next);
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

	return (
		<section className={styles.panel}>
			<header className={styles.header}>
				<h3>{t("shadowTeaching.panel.title")}</h3>
				{refreshing ? (
					<span className={styles.refreshing}>{t("common.loading")}</span>
				) : null}
			</header>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowTeaching.panel.todayLesson")}
				</span>
				<span className={styles.value}>
					{current?.lesson?.title ?? t("shadowTeaching.empty.currentLesson")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowTeaching.panel.nextCheckpoint")}
				</span>
				<span className={styles.value}>
					{current?.nextCheckpoint?.label ??
						t("shadowTeaching.panel.noneCheckpoint")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowTeaching.panel.exercisesCount")}
				</span>
				<span className={styles.value}>{current?.exercisesDue ?? 0}</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowTeaching.panel.revisionReminder")}
				</span>
				<span className={styles.value}>
					{t("shadowTeaching.currentLesson.revisionDue", {
						count: String(current?.revisionDue ?? 0),
					})}
				</span>
			</div>
		</section>
	);
}
