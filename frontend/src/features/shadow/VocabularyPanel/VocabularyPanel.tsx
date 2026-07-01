import { useTranslation } from "@/i18n/useTranslation";
import styles from "./VocabularyPanel.module.css";

export function VocabularyPanel() {
	const { t } = useTranslation();

	return (
		<section className={styles.panel}>
			<h3 className={styles.title}>{t("pipeline.shadow.vocabularyTitle")}</h3>
			<p className={styles.description}>
				{t("pipeline.shadow.vocabularyDescription")}
			</p>
		</section>
	);
}
