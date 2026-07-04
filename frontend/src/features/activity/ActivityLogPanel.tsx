import { useState } from "react";
import { useTranslation } from "@/i18n/useTranslation";
import type { ActivityLogEntry } from "./activityLogStore";
import styles from "./ActivityLogPanel.module.css";

interface ActivityLogPanelProps {
	entries: ActivityLogEntry[];
	onClear?: () => void;
}

export function ActivityLogPanel({ entries, onClear }: ActivityLogPanelProps) {
	const { t } = useTranslation();
	const [open, setOpen] = useState(false);
	const [expanded, setExpanded] = useState(true);

	const latest = entries.at(-1);
	const hasErrors = entries.some((entry) => entry.level === "error");

	return (
		<div className={styles.root} data-open={open} data-has-errors={hasErrors}>
			<button
				type="button"
				className={styles.toggle}
				onClick={() => setOpen((value) => !value)}
				aria-expanded={open}
				aria-controls="activity-log-panel"
			>
				<span className={styles.toggleLabel}>
					{t("activityLog.toggleLabel")}
				</span>
				<span className={styles.toggleCount}>{entries.length}</span>
			</button>

			{open ? (
				<section
					id="activity-log-panel"
					className={styles.panel}
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
							<button
								type="button"
								className={styles.iconButton}
								onClick={() => setExpanded((value) => !value)}
								aria-expanded={expanded}
							>
								{expanded ? "−" : "+"}
							</button>
							{onClear ? (
								<button
									type="button"
									className={styles.iconButton}
									onClick={onClear}
								>
									{t("activityLog.clear")}
								</button>
							) : null}
						</div>
					</header>

					{expanded ? (
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
					) : null}
				</section>
			) : null}
		</div>
	);
}
