import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import { Spinner } from "@/components/ui/Spinner";
import styles from "./UploadProgress.module.css";

interface UploadProgressProps {
	fileName: string;
	progress: number;
}

export function UploadProgress({ fileName, progress }: UploadProgressProps) {
	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>Uploading</p>
				<Badge variant="info">Processing</Badge>
			</div>
			<p className={styles.fileName}>{fileName}</p>
			<div className={styles.progressBlock}>
				<Spinner label="Uploading file" />
				<Progress value={progress} />
			</div>
			<p className={styles.percent}>{progress} %</p>
		</Card>
	);
}
