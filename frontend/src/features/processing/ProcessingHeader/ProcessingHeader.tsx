import { useTranslation } from "@/i18n";
import styles from "./ProcessingHeader.module.css";

interface ProcessingHeaderProps {
	title: string;
}

export function ProcessingHeader({ title }: ProcessingHeaderProps) {
	const { t } = useTranslation();

	return (
		<header className={styles.header}>
			<h2 className={styles.heading}>{t("workspace.processing.heading")}</h2>
			<p className={styles.title}>{title}</p>
		</header>
	);
}
