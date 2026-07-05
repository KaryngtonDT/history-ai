import { useTranslation } from "@/i18n/useTranslation";
import { useActivityLog } from "./ActivityLogProvider";
import styles from "./PageActivityLog.module.css";

export function PageActivityLog() {
	const { t } = useTranslation();
	const { entries, clear } = useActivityLog();

	const latest = entries.at(-1);
	const hasErrors = entries.some((entry) => entry.level === "error");

	return (
		<section
			className={styles.root}
			data-has-errors={hasErrors}
			aria-label={t("activityLog.title")}
		>
			<header className={styles.header}>
				<div>
					<p className={styles.title}>{t("activityLog.title")}</p>
					{latest ? (
						<p className={styles.latest}>{latest.message}</p>
					) : (
						<p className={styles.latest}>{t("activityLog.empty")}</p>
					)}
				</div>
				<div className={styles.headerActions}>
					<span className={styles.count}>{entries.length}</span>
					<button type="button" className={styles.clearButton} onClick={clear}>
						{t("activityLog.clear")}
					</button>
				</div>
			</header>

			<ul className={styles.list}>
				{entries.length === 0 ? (
					<li className={styles.empty}>{t("activityLog.empty")}</li>
				) : (
					entries.map((entry) => (
						<li
							key={entry.id}
							className={styles.entry}
							data-level={entry.level}
						>
							<span className={styles.time}>{entry.time}</span>
							{entry.source ? (
								<span className={styles.source}>{entry.source}</span>
							) : null}
							<span>{entry.message}</span>
						</li>
					))
				)}
			</ul>
		</section>
	);
}
