import { useTranslation } from "@/i18n/useTranslation";
import type { ExecutionOptimization } from "@/services/optimization/types";
import { OptimizationParameterList } from "../OptimizationParameterList";
import { OptimizationQualitySummary } from "../OptimizationQualitySummary";
import styles from "./OptimizationDashboard.module.css";

interface OptimizationDashboardProps {
	optimization: ExecutionOptimization | null;
	loading?: boolean;
}

export function OptimizationDashboard({
	optimization,
	loading = false,
}: OptimizationDashboardProps) {
	const { t } = useTranslation();

	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>{t("pipeline.optimization.loading")}</p>
			</div>
		);
	}

	if (!optimization) {
		return null;
	}

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>{t("pipeline.optimization.title")}</p>
				<span className={styles.badge}>{t("pipeline.optimization.badge")}</span>
			</div>

			<OptimizationQualitySummary
				profile={optimization.profile}
				estimatedImpact={optimization.estimatedImpact}
			/>

			<OptimizationParameterList stages={optimization.stages} />

			{optimization.explanations.length > 0 ? (
				<div className={styles.explanations}>
					<p className={styles.explanationTitle}>
						{t("pipeline.optimization.adjustments")}
					</p>
					<ul>
						{optimization.explanations.map((explanation) => (
							<li key={explanation}>{explanation}</li>
						))}
					</ul>
				</div>
			) : null}

			<p className={styles.summary}>{optimization.summary}</p>
		</div>
	);
}
