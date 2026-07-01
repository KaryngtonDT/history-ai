import { useTranslation } from "@/i18n";
import type { LearningInsight } from "@/services/learning/types";
import styles from "../learning.module.css";

interface LearningInsightListProps {
	insights: LearningInsight[];
}

export function LearningInsightList({ insights }: LearningInsightListProps) {
	const { t } = useTranslation();

	return (
		<section
			className={styles.card}
			aria-labelledby="learning-insights-heading"
		>
			<h2 id="learning-insights-heading" className={styles.title}>
				{t("learning.insights.title")}
			</h2>
			<p className={styles.description}>{t("learning.insights.description")}</p>
			{insights.length === 0 ? (
				<p className={styles.empty}>{t("learning.insights.empty")}</p>
			) : (
				<ul className={styles.list}>
					{insights.map((insight) => (
						<li key={insight.id} className={styles.listItem}>
							<strong>{insight.type}</strong>
							<span>{insight.summary}</span>
							<span className={styles.meta}>
								{t("learning.generatedBecause", {
									sources: insight.sourceSignalIds.length,
								})}
							</span>
						</li>
					))}
				</ul>
			)}
		</section>
	);
}
