import { Spinner } from "@/components/ui/Spinner";
import styles from "./ShadowWatchBootstrapPanel.module.css";

export type BootstrapCheckStatus =
	| "pending"
	| "active"
	| "done"
	| "warning"
	| "error";

export interface BootstrapCheckItem {
	id: string;
	label: string;
	status: BootstrapCheckStatus;
	detail?: string;
}

export interface BootstrapLogEntry {
	time: string;
	message: string;
	level: "info" | "warn" | "error";
}

export interface ShadowWatchBootstrapPanelProps {
	title: string;
	subtitle: string;
	checks: BootstrapCheckItem[];
	log: BootstrapLogEntry[];
	logTitle: string;
}

function statusSymbol(status: BootstrapCheckStatus): string {
	switch (status) {
		case "done":
			return "✓";
		case "active":
			return "…";
		case "warning":
			return "!";
		case "error":
			return "✕";
		default:
			return "○";
	}
}

export function ShadowWatchBootstrapPanel({
	title,
	subtitle,
	checks,
	log,
	logTitle,
}: ShadowWatchBootstrapPanelProps) {
	return (
		<div className={styles.root} role="status" aria-live="polite">
			<Spinner label={subtitle} />
			<div className={styles.content}>
				<h2 className={styles.title}>{title}</h2>
				<p className={styles.subtitle}>{subtitle}</p>

				<ul className={styles.checklist}>
					{checks.map((item) => (
						<li
							key={item.id}
							className={styles.checkItem}
							data-status={item.status}
						>
							<span className={styles.checkSymbol} aria-hidden="true">
								{statusSymbol(item.status)}
							</span>
							<div>
								<span className={styles.checkLabel}>{item.label}</span>
								{item.detail ? (
									<p className={styles.checkDetail}>{item.detail}</p>
								) : null}
							</div>
						</li>
					))}
				</ul>

				<div className={styles.logPanel}>
					<p className={styles.logTitle}>{logTitle}</p>
					<ul className={styles.logList}>
						{log.map((entry) => (
							<li
								key={`${entry.time}-${entry.level}-${entry.message}`}
								className={styles.logEntry}
								data-level={entry.level}
							>
								<span className={styles.logTime}>{entry.time}</span>
								<span>{entry.message}</span>
							</li>
						))}
					</ul>
				</div>
			</div>
		</div>
	);
}
