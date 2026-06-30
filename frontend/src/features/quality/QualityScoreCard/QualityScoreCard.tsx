import { qualityService } from "@/services/quality/QualityService";
import styles from "./QualityScoreCard.module.css";

interface QualityScoreCardProps {
	category: string;
	score: number;
	explanation: string;
}

export function QualityScoreCard({
	category,
	score,
	explanation,
}: QualityScoreCardProps) {
	return (
		<div className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>
					{qualityService.formatCategory(category)}
				</p>
				<p className={styles.score}>{score}</p>
			</div>
			<p className={styles.explanation}>{explanation}</p>
		</div>
	);
}
