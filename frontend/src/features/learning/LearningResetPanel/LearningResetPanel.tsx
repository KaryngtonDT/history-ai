import { useTranslation } from "@/i18n";
import styles from "../learning.module.css";

interface LearningResetPanelProps {
	onReset: () => Promise<void>;
	disabled?: boolean;
}

export function LearningResetPanel({
	onReset,
	disabled = false,
}: LearningResetPanelProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.card} aria-labelledby="learning-reset-heading">
			<h2 id="learning-reset-heading" className={styles.title}>
				{t("learning.reset.title")}
			</h2>
			<p className={styles.description}>{t("learning.reset.description")}</p>
			<button
				type="button"
				className={styles.resetButton}
				disabled={disabled}
				onClick={() => void onReset()}
			>
				{t("learning.reset.action")}
			</button>
		</section>
	);
}
