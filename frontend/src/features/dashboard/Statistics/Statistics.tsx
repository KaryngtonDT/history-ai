import { Card } from "@/components/ui/Card";
import type { DashboardStatistics } from "@/services/dashboard/types";
import styles from "./Statistics.module.css";

interface StatisticsProps {
	statistics: DashboardStatistics;
}

const statItems: { key: keyof DashboardStatistics; label: string }[] = [
	{ key: "contents", label: "Contents" },
	{ key: "completed", label: "Completed" },
	{ key: "processing", label: "Processing" },
	{ key: "artifacts", label: "Artifacts" },
];

export function Statistics({ statistics }: StatisticsProps) {
	return (
		<section className={styles.root} aria-labelledby="statistics-heading">
			<h3 id="statistics-heading" className={styles.heading}>
				Statistics
			</h3>
			<div className={styles.grid}>
				{statItems.map(({ key, label }) => (
					<Card key={key} className={styles.statCard}>
						<p className={styles.label}>{label}</p>
						<p className={styles.value}>{statistics[key]}</p>
					</Card>
				))}
			</div>
		</section>
	);
}
