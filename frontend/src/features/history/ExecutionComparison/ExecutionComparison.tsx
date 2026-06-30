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
	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>Comparing versions...</p>
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
					Compare V{comparison.leftVersion} vs V{comparison.rightVersion}
				</p>
			</div>

			{comparison.providerDifferences.length > 0 ? (
				<div className={styles.section}>
					<p className={styles.sectionTitle}>Provider differences</p>
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
					<p className={styles.sectionTitle}>Optimization</p>
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
					<p className={styles.sectionTitle}>Quality score</p>
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
