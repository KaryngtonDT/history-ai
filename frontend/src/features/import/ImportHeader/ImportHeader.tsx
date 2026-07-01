import { useTranslation } from "@/i18n";
import styles from "./ImportHeader.module.css";

export function ImportHeader() {
	const { t } = useTranslation();

	return (
		<header className={styles.header}>
			<h2 className={styles.title}>{t("workspace.import.headerTitle")}</h2>
			<p className={styles.description}>
				{t("workspace.import.headerDescription")}
			</p>
		</header>
	);
}
