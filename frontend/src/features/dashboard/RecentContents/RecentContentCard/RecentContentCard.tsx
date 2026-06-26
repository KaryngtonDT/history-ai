import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import type { Content } from "@/services/content/types";
import styles from "./RecentContentCard.module.css";

interface RecentContentCardProps {
	content: Content;
}

function statusLabel(status: Content["status"]): string {
	return status === "processing" ? "Processing" : "Completed";
}

function statusVariant(status: Content["status"]): "warning" | "success" {
	return status === "processing" ? "warning" : "success";
}

export function RecentContentCard({ content }: RecentContentCardProps) {
	const isProcessing = content.status === "processing";

	const handleClick = () => {
		console.log(`/content/${content.id}`);
	};

	return (
		<Card
			className={styles.card}
			role="button"
			tabIndex={0}
			onClick={handleClick}
			onKeyDown={(event) => {
				if (event.key === "Enter" || event.key === " ") {
					event.preventDefault();
					handleClick();
				}
			}}
		>
			<div className={styles.header}>
				<h4 className={styles.title}>{content.title}</h4>
				<Badge variant={statusVariant(content.status)}>
					{statusLabel(content.status)}
				</Badge>
			</div>
			{isProcessing ? (
				<div className={styles.progress}>
					<Progress value={content.progress} />
					<span className={styles.progressLabel}>{content.progress} %</span>
				</div>
			) : null}
		</Card>
	);
}
