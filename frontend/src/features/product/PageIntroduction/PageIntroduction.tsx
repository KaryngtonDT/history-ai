import type { ReactNode } from "react";
import { useTranslation } from "@/i18n";
import styles from "./PageIntroduction.module.css";

interface PageIntroductionProps {
	eyebrow?: string;
	title: string;
	description: string;
	whatCanIDo?: string;
	primaryAction?: ReactNode;
	secondaryActions?: ReactNode;
}

export function PageIntroduction({
	eyebrow,
	title,
	description,
	whatCanIDo,
	primaryAction,
	secondaryActions,
}: PageIntroductionProps) {
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
				<div className={styles.helpBox}>
					<p className={styles.helpTitle}>{t("shell.pageIntro.whatCanIDo")}</p>
					<p className={styles.helpText}>{whatCanIDo}</p>
				</div>
			) : null}
		</header>
	);
}
