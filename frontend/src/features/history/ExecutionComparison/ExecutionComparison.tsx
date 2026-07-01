import { useTranslation } from "@/i18n";
import { historyService } from "@/services/history/HistoryService";
import type { ComparisonResult } from "@/services/history/types";
import styles from "./ExecutionComparison.module.css";

interface ExecutionComparisonProps {
	comparison: ComparisonResult | null;
	loading?: boolean;
}

export function ExecutionComparison({
	comparison,
	loading = false,
}: ExecutionComparisonProps) {
	const { t } = useTranslation();

	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>
					{t("workspace.history.comparingVersions")}
				</p>
			</div>
		);
	}

	if (!comparison) {
		return null;
	}

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>
					{t("workspace.history.compareVersions", {
						left: comparison.leftVersion,
						right: comparison.rightVersion,
					})}
				</p>
			</div>

			{comparison.providerDifferences.length > 0 ? (
				<div className={styles.section}>
					<p className={styles.sectionTitle}>
						{t("workspace.history.providerDifferences")}
					</p>
					<ul>
						{comparison.providerDifferences.map((difference) => (
							<li key={difference.stage}>
								{difference.stage}: {difference.leftProvider} →{" "}
								{difference.rightProvider}
							</li>
						))}
					</ul>
				</div>
			) : null}

			{comparison.optimizationDifference ? (
				<div className={styles.section}>
					<p className={styles.sectionTitle}>
						{t("workspace.history.optimization")}
					</p>
					<p>
						{historyService.formatProfile(
							comparison.optimizationDifference.leftProfile,
						)}{" "}
						→{" "}
						{historyService.formatProfile(
							comparison.optimizationDifference.rightProfile,
						)}
					</p>
				</div>
			) : null}

			{comparison.qualityScoreDifference ? (
				<div className={styles.section}>
					<p className={styles.sectionTitle}>
						{t("workspace.history.qualityScore")}
					</p>
					<p>
						{comparison.qualityScoreDifference.leftScore} →{" "}
						{comparison.qualityScoreDifference.rightScore} (
						{comparison.qualityScoreDifference.delta > 0 ? "+" : ""}
						{comparison.qualityScoreDifference.delta})
					</p>
				</div>
			) : null}
		</div>
	);
}
