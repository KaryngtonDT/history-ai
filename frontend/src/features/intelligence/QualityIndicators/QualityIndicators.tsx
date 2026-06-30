import { videoIntelligenceService } from "@/services/intelligence/VideoIntelligenceService";
import type { PipelineRecommendation } from "@/services/orchestrator/types";
import { PROCESSING_STRATEGY_LABELS } from "@/services/orchestrator/types";
import styles from "./QualityIndicators.module.css";

interface QualityIndicatorsProps {
	recommendation: PipelineRecommendation | null;
	confidence: number;
}

export function QualityIndicators({
	recommendation,
	confidence,
}: QualityIndicatorsProps) {
	return (
		<div className={styles.root}>
			<div>
				<p className={styles.label}>STT Confidence</p>
				<p className={styles.value}>
					{videoIntelligenceService.formatConfidence(confidence)}
				</p>
			</div>
			{recommendation ? (
				<div>
					<p className={styles.label}>Recommendation</p>
					<p className={styles.stars}>
						{videoIntelligenceService.formatQualityStars(
							recommendation.estimatedQuality,
						)}
					</p>
					<p className={styles.strategy}>
						{PROCESSING_STRATEGY_LABELS[recommendation.strategy]}
					</p>
				</div>
			) : null}
		</div>
	);
}
