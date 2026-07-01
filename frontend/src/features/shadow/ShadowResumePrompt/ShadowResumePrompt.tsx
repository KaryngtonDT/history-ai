import { useTranslation } from "@/i18n/useTranslation";
import styles from "./ShadowResumePrompt.module.css";

interface ShadowResumePromptProps {
	disabled?: boolean;
	onResume: () => void;
}

export function ShadowResumePrompt({
	disabled = false,
	onResume,
}: ShadowResumePromptProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.panel} aria-live="polite">
			<p className={styles.text}>{t("pipeline.shadow.resumePrompt")}</p>
			<button
				type="button"
				className={styles.button}
				disabled={disabled}
				onClick={onResume}
			>
				{t("pipeline.shadow.continueWatching")}
			</button>
		</section>
	);
}
