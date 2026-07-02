import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowMentorService } from "@/services/shadowMentor/ShadowMentorService";
import type { MentorDashboard } from "@/services/shadowMentor/types";
import styles from "./ShadowMentorPanel.module.css";

const POLL_INTERVAL_MS = 5000;

export function ShadowMentorPanel() {
	const { t } = useTranslation();
	const [dashboard, setDashboard] = useState<MentorDashboard | null>(null);
	const [refreshing, setRefreshing] = useState(false);

	useEffect(() => {
		let cancelled = false;

		async function refresh() {
			setRefreshing(true);

			try {
				const next = await shadowMentorService.getDashboard();
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

	const topImpact = dashboard?.goalImpact[0];

	return (
		<section className={styles.panel}>
			<header className={styles.header}>
				<h3>{t("shadowMentor.panel.title")}</h3>
				{refreshing ? (
					<span className={styles.refreshing}>{t("common.loading")}</span>
				) : null}
			</header>
			<div className={styles.card}>
				<span className={styles.label}>{t("shadowMentor.panel.goal")}</span>
				<span className={styles.value}>
					{dashboard?.primaryGoal?.title ?? t("shadowMentor.empty.goal")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowMentor.panel.currentMission")}
				</span>
				<span className={styles.value}>
					{dashboard?.currentMission?.title ?? t("shadowMentor.empty.mission")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowMentor.panel.nextMilestone")}
				</span>
				<span className={styles.value}>
					{dashboard?.nextMilestone?.label ?? t("shadowMentor.empty.milestone")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>{t("shadowMentor.panel.impact")}</span>
				<span className={styles.value}>
					{topImpact
						? t("shadowMentor.panel.impactValue", {
								percent: String(topImpact.impactPercent),
							})
						: t("shadowMentor.empty.goalImpact")}
				</span>
			</div>
		</section>
	);
}
