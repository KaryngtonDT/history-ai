import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import styles from "./DashboardPage.module.css";

export function DashboardPage() {
	return (
		<section>
			<h2 className={styles.title}>Dashboard</h2>
			<Card className={styles.card}>
				<div className={styles.cardContent}>
					<p className={styles.description}>Welcome to History AI</p>
					<Badge variant="info">Preview</Badge>
				</div>
			</Card>
		</section>
	);
}
