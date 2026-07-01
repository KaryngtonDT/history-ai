import { useTranslation } from "@/i18n/useTranslation";
import styles from "./VideoUploadHeader.module.css";

export function VideoUploadHeader() {
	const { t } = useTranslation();

	return (
		<header className={styles.header}>
			<h2 className={styles.title}>{t("shell.nav.items.upload.label")}</h2>
			<p className={styles.description}>
				{t("pipeline.upload.videoDropDescription")}
			</p>
		</header>
	);
}
