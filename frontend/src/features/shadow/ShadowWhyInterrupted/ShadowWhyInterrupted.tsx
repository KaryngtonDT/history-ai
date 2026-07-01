import { useTranslation } from "@/i18n/useTranslation";
import styles from "./ShadowWhyInterrupted.module.css";

interface ShadowWhyInterruptedProps {
	reason: string;
}

export function ShadowWhyInterrupted({ reason }: ShadowWhyInterruptedProps) {
	const { t } = useTranslation();

	return (
		<div className={styles.why}>
			<p className={styles.title}>{t("pipeline.shadow.whyInterrupted")}</p>
			<p className={styles.text}>{reason}</p>
		</div>
	);
}
