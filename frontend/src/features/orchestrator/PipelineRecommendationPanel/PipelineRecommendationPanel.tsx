import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import { PROCESSING_STRATEGY_LABELS } from "@/services/orchestrator/types";
import styles from "./PipelineRecommendationPanel.module.css";

interface PipelineRecommendationPanelProps {
	recommendation: PipelineRecommendation | null;
	loading?: boolean;
}

export function PipelineRecommendationPanel({
	recommendation,
	loading = false,
}: PipelineRecommendationPanelProps) {
	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>Loading recommendation...</p>
			</div>
		);
	}

	if (!recommendation) {
		return null;
	}

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>Pipeline Recommendation</p>
				<span className={styles.badge}>Recommended</span>
			</div>

			<div className={styles.metrics}>
				<div>
					<p className={styles.metricLabel}>Strategy</p>
					<p className={styles.metricValue}>
						{PROCESSING_STRATEGY_LABELS[recommendation.strategy]}
					</p>
				</div>
				<div>
					<p className={styles.metricLabel}>Estimated duration</p>
					<p className={styles.metricValue}>
						{orchestratorService.formatEstimatedDuration(
							recommendation.estimatedDurationSeconds,
						)}
					</p>
				</div>
				<div>
					<p className={styles.metricLabel}>Estimated quality</p>
					<p className={styles.metricValue}>
						{orchestratorService.formatQualityStars(
							recommendation.estimatedQuality,
						)}
					</p>
				</div>
			</div>

			<p className={styles.metricLabel}>Estimated VRAM</p>
			<p className={styles.metricValue}>{recommendation.estimatedVramGb} GB</p>

			<p className={styles.explanation}>{recommendation.explanation}</p>
		</div>
	);
}
