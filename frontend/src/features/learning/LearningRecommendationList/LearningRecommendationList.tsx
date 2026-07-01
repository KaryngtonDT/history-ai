import { useTranslation } from "@/i18n";
import type { LearningRecommendation } from "@/services/learning/types";
import styles from "../learning.module.css";

interface LearningRecommendationListProps {
	recommendations: LearningRecommendation[];
}

export function LearningRecommendationList({
	recommendations,
}: LearningRecommendationListProps) {
	const { t } = useTranslation();

	return (
		<section
			className={styles.card}
			aria-labelledby="learning-recommendations-heading"
		>
			<h2 id="learning-recommendations-heading" className={styles.title}>
				{t("learning.recommendations.title")}
			</h2>
			<p className={styles.description}>
				{t("learning.recommendations.description")}
			</p>
			{recommendations.length === 0 ? (
				<p className={styles.empty}>{t("learning.recommendations.empty")}</p>
			) : (
				<ul className={styles.list}>
					{recommendations.map((recommendation) => (
						<li key={recommendation.id} className={styles.listItem}>
							<strong>{recommendation.type}</strong>
							<span>{recommendation.explanation}</span>
							<span className={styles.meta}>
								{t("learning.recommendationReason", {
									sources: recommendation.sourceInsightIds.length,
								})}
							</span>
						</li>
					))}
				</ul>
			)}
		</section>
	);
}
