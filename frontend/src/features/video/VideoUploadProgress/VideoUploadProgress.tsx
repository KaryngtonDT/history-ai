import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import { Spinner } from "@/components/ui/Spinner";
import styles from "./VideoUploadProgress.module.css";

interface VideoUploadProgressProps {
	fileName: string;
	progress: number;
}

export function VideoUploadProgress({
	fileName,
	progress,
}: VideoUploadProgressProps) {
	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>Uploading</p>
				<Badge variant="info">In progress</Badge>
			</div>
			<p className={styles.fileName}>{fileName}</p>
			<div className={styles.progressBlock}>
				<Spinner label="Uploading video" />
				<Progress value={progress} />
			</div>
			<p className={styles.percent}>{progress} %</p>
		</Card>
	);
}
