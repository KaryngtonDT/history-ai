import { useTranslation } from "@/i18n/useTranslation";
import styles from "./RecommendationReasons.module.css";

interface RecommendationReasonsProps {
	reasons: string[];
}

export function RecommendationReasons({ reasons }: RecommendationReasonsProps) {
	const { t } = useTranslation();

	if (reasons.length === 0) {
		return null;
	}

	return (
		<div className={styles.root}>
			<p className={styles.title}>{t("pipeline.intelligence.reasons")}</p>
			<ol className={styles.list}>
				{reasons.map((reason) => (
					<li key={reason}>{reason}</li>
				))}
			</ol>
		</div>
	);
}
