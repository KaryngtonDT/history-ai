import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import styles from "./ProcessingSummary.module.css";

interface ProcessingSummaryProps {
	title: string;
}

export function ProcessingSummary({ title }: ProcessingSummaryProps) {
	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.title}>Processing complete</p>
				<Badge variant="success">Ready</Badge>
			</div>
			<p className={styles.message}>
				{title} has been transformed into learning artifacts. They will appear
				in your library shortly.
			</p>
		</Card>
	);
}
