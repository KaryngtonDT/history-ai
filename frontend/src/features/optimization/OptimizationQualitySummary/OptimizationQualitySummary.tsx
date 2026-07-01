import { useTranslation } from "@/i18n/useTranslation";
import { optimizationService } from "@/services/optimization/OptimizationService";
import styles from "./OptimizationQualitySummary.module.css";

interface OptimizationQualitySummaryProps {
	profile: string;
	estimatedImpact: number;
}

export function OptimizationQualitySummary({
	profile,
	estimatedImpact,
}: OptimizationQualitySummaryProps) {
	const { t } = useTranslation();

	return (
		<div className={styles.root}>
			<div>
				<p className={styles.label}>{t("pipeline.optimization.profile")}</p>
				<p className={styles.value}>
					{optimizationService.formatProfile(profile)}
				</p>
			</div>
			<div>
				<p className={styles.label}>{t("pipeline.optimization.impact")}</p>
				<p className={styles.stars}>
					{optimizationService.formatImpactStars(estimatedImpact)}
				</p>
			</div>
		</div>
	);
}
