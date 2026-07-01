import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n";
import styles from "./ProcessingSummary.module.css";

interface ProcessingSummaryProps {
	title: string;
}

export function ProcessingSummary({ title }: ProcessingSummaryProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.title}>{t("workspace.processing.complete")}</p>
				<Badge variant="success">{t("workspace.processing.ready")}</Badge>
			</div>
			<p className={styles.message}>
				{t("workspace.processing.completeMessage", { title })}
			</p>
		</Card>
	);
}
