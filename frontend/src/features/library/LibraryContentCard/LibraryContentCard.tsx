import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import type { Content } from "@/services/content/types";
import styles from "./LibraryContentCard.module.css";

interface LibraryContentCardProps {
	content: Content;
}

function sourceTypeLabel(sourceType: Content["sourceType"]): string {
	const labels: Record<Content["sourceType"], string> = {
		pdf: "PDF",
		audio: "Audio",
		video: "Video",
		youtube: "YouTube",
	};
	return labels[sourceType];
}

function statusLabel(status: Content["status"]): string {
	return status === "processing" ? "Processing" : "Completed";
}

function statusVariant(status: Content["status"]): "warning" | "success" {
	return status === "processing" ? "warning" : "success";
}

export function LibraryContentCard({ content }: LibraryContentCardProps) {
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
				<h3 className={styles.title}>{content.title}</h3>
				<div className={styles.badges}>
					<Badge variant="neutral">{sourceTypeLabel(content.sourceType)}</Badge>
					<Badge variant={statusVariant(content.status)}>
						{statusLabel(content.status)}
					</Badge>
				</div>
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
