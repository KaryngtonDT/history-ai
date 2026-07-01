import { Link } from "react-router";
import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import type { Content } from "@/services/content/domain/Content";
import { workItemService } from "@/services/workItem/WorkItemService";
import { mapContentToWorkItem } from "@/services/workItem/workItemMappers";
import styles from "./RecentContentCard.module.css";

interface RecentContentCardProps {
	content: Content;
}

function statusVariant(
	status: Content["status"],
): "warning" | "success" | "neutral" {
	return status === "processing" ? "warning" : "success";
}

export function RecentContentCard({ content }: RecentContentCardProps) {
	const workItem = mapContentToWorkItem(content);
	const isProcessing = content.status === "processing";

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<span className={styles.icon} aria-hidden="true">
					{workItem.icon}
				</span>
				<div className={styles.metaBlock}>
					<h4 className={styles.title}>{content.title}</h4>
					<p className={styles.meta}>
						{workItemService.formatTypeLabel(workItem.type)} ·{" "}
						{workItem.currentStep}
					</p>
				</div>
				<Badge variant={statusVariant(content.status)}>
					{workItemService.formatStatusLabel(workItem.status)}
				</Badge>
			</div>
			{isProcessing ? (
				<div className={styles.progress}>
					<Progress value={content.progress} />
					<span className={styles.progressLabel}>{content.progress} %</span>
				</div>
			) : null}
			<Link
				to={workItem.openRoute}
				className={styles.openLink}
				aria-label={`Open ${content.title}`}
			>
				Open →
			</Link>
		</Card>
	);
}
