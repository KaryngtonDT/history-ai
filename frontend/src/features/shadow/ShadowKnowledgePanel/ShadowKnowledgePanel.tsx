import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowKnowledgeService } from "@/services/shadowKnowledge/ShadowKnowledgeService";
import type { KnowledgeRadar } from "@/services/shadowKnowledge/types";
import styles from "./ShadowKnowledgePanel.module.css";

const POLL_INTERVAL_MS = 5000;

export function ShadowKnowledgePanel() {
	const { t } = useTranslation();
	const [radar, setRadar] = useState<KnowledgeRadar | null>(null);
	const [nodeCount, setNodeCount] = useState(0);
	const [refreshing, setRefreshing] = useState(false);

	useEffect(() => {
		let cancelled = false;

		async function refresh() {
			setRefreshing(true);

			try {
				const [graph, gapsResponse] = await Promise.all([
					shadowKnowledgeService.getGraph(),
					shadowKnowledgeService.getGaps("kubernetes"),
				]);

				if (!cancelled) {
					setNodeCount(graph.nodes.length);
					setRadar(gapsResponse.radar);
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

	const topGap = radar?.gaps[0];

	return (
		<section className={styles.panel}>
			<header className={styles.header}>
				<h3>{t("shadowKnowledge.panel.title")}</h3>
				{refreshing ? (
					<span className={styles.refreshing}>{t("common.loading")}</span>
				) : null}
			</header>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowKnowledge.panel.goal")}
				</span>
				<span className={styles.value}>
					{radar?.goalLabel ?? t("shadowKnowledge.gaps.defaultGoal")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowKnowledge.panel.readiness")}
				</span>
				<span className={styles.value}>{radar?.readinessPercent ?? 0}%</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowKnowledge.panel.topGap")}
				</span>
				<span className={styles.value}>
					{topGap?.label ?? t("shadowKnowledge.panel.noGap")}
				</span>
			</div>
			<div className={styles.card}>
				<span className={styles.label}>
					{t("shadowKnowledge.panel.nodes")}
				</span>
				<span className={styles.value}>{nodeCount}</span>
			</div>
		</section>
	);
}
