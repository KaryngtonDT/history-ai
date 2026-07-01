import type { ReactNode } from "react";
import { useTranslation } from "@/i18n";
import styles from "./CompactPageIntroduction.module.css";

interface CompactPageIntroductionProps {
	eyebrow?: string;
	title: string;
	description: string;
	whatCanIDo?: string;
	primaryAction?: ReactNode;
	secondaryActions?: ReactNode;
}

export function CompactPageIntroduction({
	eyebrow,
	title,
	description,
	whatCanIDo,
	primaryAction,
	secondaryActions,
}: CompactPageIntroductionProps) {
	const { t } = useTranslation();

	return (
		<header className={styles.root}>
			<div className={styles.headerRow}>
				<div>
					{eyebrow ? <p className={styles.eyebrow}>{eyebrow}</p> : null}
					<h1 className={styles.title}>{title}</h1>
					<p className={styles.description}>{description}</p>
				</div>
				{primaryAction || secondaryActions ? (
					<div className={styles.actions}>
						{primaryAction}
						{secondaryActions}
					</div>
				) : null}
			</div>
			{whatCanIDo ? (
				<details className={styles.helpDetails}>
					<summary className={styles.helpSummary}>
						{t("shell.pageIntro.whatCanIDo")}
					</summary>
					<p className={styles.helpText}>{whatCanIDo}</p>
				</details>
			) : null}
		</header>
	);
}
