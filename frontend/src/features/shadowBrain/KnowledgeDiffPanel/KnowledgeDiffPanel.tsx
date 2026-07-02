import { useEffect, useState } from "react";
import { useTranslation } from "@/i18n";
import { shadowBrainService } from "@/services/shadowBrain/ShadowBrainService";
import type { KnowledgeDiff } from "@/services/shadowBrain/types";
import styles from "./KnowledgeDiffPanel.module.css";

interface KnowledgeDiffPanelProps {
	resourceType?: string;
	resourceId?: string;
}

function redundancyClass(redundancy: KnowledgeDiff["redundancy"]): string {
	switch (redundancy) {
		case "low":
			return styles.redundancyLow;
		case "high":
			return styles.redundancyHigh;
		default:
			return styles.redundancyMedium;
	}
}

export function KnowledgeDiffPanel({
	resourceType = "video",
	resourceId = "",
}: KnowledgeDiffPanelProps) {
	const { t } = useTranslation();
	const [diff, setDiff] = useState<KnowledgeDiff | null>(null);
	const [loading, setLoading] = useState(false);

	useEffect(() => {
		if (resourceId.trim() === "") {
			setDiff(null);
			return;
		}

		let cancelled = false;

		async function load() {
			setLoading(true);

			try {
				const next = await shadowBrainService.getDiff(resourceType, resourceId);
				if (!cancelled) {
					setDiff(next);
				}
			} finally {
				if (!cancelled) {
					setLoading(false);
				}
			}
		}

		void load();

		return () => {
			cancelled = true;
		};
	}, [resourceId, resourceType]);

	if (resourceId.trim() === "") {
		return null;
	}

	return (
		<section className={styles.panel}>
			<header className={styles.header}>
				<h3>{t("shadowBrain.diff.title")}</h3>
				{loading ? (
					<span className={styles.refreshing}>{t("common.loading")}</span>
				) : null}
			</header>

			{!diff && !loading ? (
				<p className={styles.empty}>{t("shadowBrain.diff.empty")}</p>
			) : null}

			{diff ? (
				<div className={styles.summary}>
					<p className={styles.resourceLabel}>{diff.resourceLabel}</p>
					<p className={styles.meta}>
						{t("shadowBrain.diff.overlap", {
							percent: String(diff.redundancyPercent),
						})}
					</p>
					<p className={`${styles.meta} ${redundancyClass(diff.redundancy)}`}>
						{t("shadowBrain.diff.redundancyLabel", {
							value: diff.redundancy,
						})}
					</p>

					<div className={styles.statsGrid}>
						<div className={styles.statCard}>
							<span className={styles.statLabel}>
								{t("shadowBrain.diff.newConcepts")}
							</span>
							<span className={styles.statValue}>{diff.newConcepts}</span>
						</div>
						<div className={styles.statCard}>
							<span className={styles.statLabel}>
								{t("shadowBrain.diff.knownConcepts")}
							</span>
							<span className={styles.statValue}>{diff.knownConcepts}</span>
						</div>
						<div className={styles.statCard}>
							<span className={styles.statLabel}>
								{t("shadowBrain.diff.revisionDue")}
							</span>
							<span className={styles.statValue}>{diff.revisionDue}</span>
						</div>
						<div className={styles.statCard}>
							<span className={styles.statLabel}>
								{t("shadowBrain.diff.redundancyPercent")}
							</span>
							<span className={styles.statValue}>
								{diff.redundancyPercent}%
							</span>
						</div>
					</div>

					{diff.novelConceptKeys.length > 0 ? (
						<div className={styles.tagList}>
							{diff.novelConceptKeys.map((key) => (
								<span key={key} className={styles.tag}>
									{key.replace(/_/g, " ")}
								</span>
							))}
						</div>
					) : null}

					{diff.revisionConceptKeys.length > 0 ? (
						<div className={styles.tagList}>
							{diff.revisionConceptKeys.map((key) => (
								<span
									key={key}
									className={`${styles.tag} ${styles.tagRevision}`}
								>
									{key.replace(/_/g, " ")}
								</span>
							))}
						</div>
					) : null}
				</div>
			) : null}
		</section>
	);
}
