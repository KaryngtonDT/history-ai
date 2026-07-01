import { useTranslation } from "@/i18n";
import styles from "./LibraryHeader.module.css";

export function LibraryHeader() {
	const { t } = useTranslation();

	return (
		<header className={styles.header}>
			<h2 className={styles.title}>{t("workspace.library.headerTitle")}</h2>
			<p className={styles.description}>
				{t("workspace.library.headerDescription")}
			</p>
		</header>
	);
}
