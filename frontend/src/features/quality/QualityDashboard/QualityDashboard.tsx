import { useTranslation } from "@/i18n/useTranslation";
import { qualityService } from "@/services/quality/QualityService";
import type { QualityReport } from "@/services/quality/types";
import { QualityRecommendation } from "../QualityRecommendation";
import { QualityScoreCard } from "../QualityScoreCard";
import styles from "./QualityDashboard.module.css";

interface QualityDashboardProps {
	report: QualityReport | null;
	loading?: boolean;
}

export function QualityDashboard({
	report,
	loading = false,
}: QualityDashboardProps) {
	const { t } = useTranslation();

	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>
					{t("pipeline.qualityDashboard.loading")}
				</p>
			</div>
		);
	}

	if (!report) {
		return null;
	}

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>{t("pipeline.qualityDashboard.title")}</p>
				<span className={styles.badge}>
					{t("pipeline.qualityDashboard.badge")}
				</span>
			</div>

			<div className={styles.overall}>
				<p className={styles.overallLabel}>
					{t("pipeline.qualityDashboard.overallScore")}
				</p>
				<p className={styles.overallScore}>
					{report.overallScore}
					<span className={styles.overallMax}>/ 100</span>
				</p>
			</div>

			<div className={styles.divider} />

			<div className={styles.metrics}>
				{qualityService.sortedMetrics(report.metrics).map((metric) => (
					<QualityScoreCard
						key={metric.category}
						category={metric.category}
						score={metric.score}
						explanation={metric.explanation}
					/>
				))}
			</div>

			<QualityRecommendation recommendation={report.recommendation} />

			{report.explanations.length > 0 ? (
				<div className={styles.explanations}>
					<p className={styles.explanationTitle}>
						{t("pipeline.qualityDashboard.assessmentNotes")}
					</p>
					<ul>
						{report.explanations.map((explanation) => (
							<li key={explanation}>{explanation}</li>
						))}
					</ul>
				</div>
			) : null}
		</div>
	);
}
