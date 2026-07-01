import { Link } from "react-router";
import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n";
import type { WorkItemSummary } from "@/services/workItem/types";
import styles from "./ActionableStats.module.css";

interface ActionableStatsProps {
	summary: WorkItemSummary;
}

const STAT_KEYS = [
	"videoCount",
	"projectCount",
	"completedCount",
	"artifactCount",
] as const;

const STAT_LOCALE_KEYS = {
	videoCount: "videos",
	projectCount: "projects",
	completedCount: "completed",
	artifactCount: "artifacts",
} as const;

const STAT_ROUTES = {
	videoCount: "/workspace",
	projectCount: "/workspace",
	completedCount: "/library",
	artifactCount: "/library",
} as const;

export function ActionableStats({ summary }: ActionableStatsProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.root} aria-labelledby="stats-heading">
			<h2 id="stats-heading" className={styles.heading}>
				{t("home.stats.heading")}
			</h2>
			<div className={styles.grid}>
				{STAT_KEYS.map((key) => {
					const localeKey = STAT_LOCALE_KEYS[key];
					const label = t(`home.stats.${localeKey}.label`);
					const description = t(`home.stats.${localeKey}.description`);
					const action = t(`home.stats.${localeKey}.action`);

					return (
						<Link
							key={key}
							to={STAT_ROUTES[key]}
							className={styles.statLink}
							aria-label={t("home.stats.ariaLabel", {
								label,
								count: summary[key],
								action,
							})}
						>
							<Card className={styles.card}>
								<p className={styles.value}>{summary[key]}</p>
								<p className={styles.label}>{label}</p>
								<p className={styles.description}>{description}</p>
								<span className={styles.action}>{action}</span>
							</Card>
						</Link>
					);
				})}
			</div>
		</section>
	);
}
