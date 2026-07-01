import { Link } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import { useTranslation } from "@/i18n";
import type { WorkItem } from "@/services/workItem/types";
import styles from "./RecentWorkList.module.css";

interface RecentWorkListProps {
	items: WorkItem[];
}

function statusVariant(
	status: WorkItem["status"],
): "warning" | "success" | "neutral" {
	if (status === "processing") {
		return "warning";
	}

	if (status === "completed") {
		return "success";
	}

	return "neutral";
}

export function RecentWorkList({ items }: RecentWorkListProps) {
	const { t } = useTranslation();

	if (items.length === 0) {
		return (
			<section className={styles.root} aria-labelledby="recent-work-heading">
				<h2 id="recent-work-heading" className={styles.heading}>
					{t("home.recent.heading")}
				</h2>
				<p className={styles.empty}>{t("home.recent.empty")}</p>
			</section>
		);
	}

	return (
		<section className={styles.root} aria-labelledby="recent-work-heading">
			<h2 id="recent-work-heading" className={styles.heading}>
				{t("home.recent.heading")}
			</h2>
			<ul className={styles.list}>
				{items.map((item) => (
					<li key={`${item.type}-${item.id}`}>
						<Card className={styles.card}>
							<div className={styles.header}>
								<span className={styles.icon} aria-hidden="true">
									{item.icon}
								</span>
								<div className={styles.metaBlock}>
									<p className={styles.title}>{item.title}</p>
									<p className={styles.meta}>
										{t(`workItem.types.${item.type}`)} · {item.currentStep}
									</p>
								</div>
								<Badge variant={statusVariant(item.status)}>
									{t(`workItem.statuses.${item.status}`)}
								</Badge>
							</div>
							{item.status === "processing" ? (
								<div className={styles.progressRow}>
									<Progress value={item.progress} />
									<span className={styles.progressLabel}>{item.progress}%</span>
								</div>
							) : null}
							<Link
								to={item.openRoute}
								className={styles.openLink}
								aria-label={t("home.recent.openAria", { title: item.title })}
							>
								{t("home.recent.open")}
							</Link>
						</Card>
					</li>
				))}
			</ul>
		</section>
	);
}
