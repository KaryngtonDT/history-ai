import { Link } from "react-router";
import { Card } from "@/components/ui/Card";
import type { WorkItemSummary } from "@/services/workItem/types";
import styles from "./ActionableStats.module.css";

interface ActionableStatsProps {
	summary: WorkItemSummary;
}

const STAT_LINKS = [
	{
		key: "videoCount" as const,
		label: "Videos",
		description: "Active video work",
		route: "/workspace",
		action: "View workspace →",
	},
	{
		key: "projectCount" as const,
		label: "Projects",
		description: "Batch and team projects",
		route: "/workspace",
		action: "Open projects →",
	},
	{
		key: "completedCount" as const,
		label: "Completed",
		description: "Finished work items",
		route: "/library",
		action: "Open library →",
	},
	{
		key: "artifactCount" as const,
		label: "Artifacts",
		description: "Generated outputs",
		route: "/library",
		action: "Browse artifacts →",
	},
];

export function ActionableStats({ summary }: ActionableStatsProps) {
	return (
		<section className={styles.root} aria-labelledby="stats-heading">
			<h2 id="stats-heading" className={styles.heading}>
				At a glance
			</h2>
			<div className={styles.grid}>
				{STAT_LINKS.map((stat) => (
					<Link
						key={stat.key}
						to={stat.route}
						className={styles.statLink}
						aria-label={`${stat.label}: ${summary[stat.key]}. ${stat.action}`}
					>
						<Card className={styles.card}>
							<p className={styles.value}>{summary[stat.key]}</p>
							<p className={styles.label}>{stat.label}</p>
							<p className={styles.description}>{stat.description}</p>
							<span className={styles.action}>{stat.action}</span>
						</Card>
					</Link>
				))}
			</div>
		</section>
	);
}
