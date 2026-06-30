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
	return (
		<div className={styles.root}>
			<div>
				<p className={styles.label}>Optimization profile</p>
				<p className={styles.value}>
					{optimizationService.formatProfile(profile)}
				</p>
			</div>
			<div>
				<p className={styles.label}>Estimated impact</p>
				<p className={styles.stars}>
					{optimizationService.formatImpactStars(estimatedImpact)}
				</p>
			</div>
		</div>
	);
}
