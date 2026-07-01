import { useTranslation } from "@/i18n/useTranslation";
import { qualityService } from "@/services/quality/QualityService";
import styles from "./QualityRecommendation.module.css";

interface QualityRecommendationProps {
	recommendation: string;
}

export function QualityRecommendation({
	recommendation,
}: QualityRecommendationProps) {
	const { t } = useTranslation();
	const ready = qualityService.isReadyForPublishing(recommendation);
	const review = qualityService.needsReview(recommendation);
	const regenerate = qualityService.needsRegeneration(recommendation);

	return (
		<div
			className={`${styles.root} ${ready ? styles.ready : ""} ${review ? styles.review : ""} ${regenerate ? styles.regenerate : ""}`}
		>
			<p className={styles.label}>
				{t("pipeline.qualityDashboard.recommendation")}
			</p>
			<p className={styles.value}>
				{ready ? "✓ " : ""}
				{qualityService.formatRecommendation(recommendation)}
			</p>
		</div>
	);
}
