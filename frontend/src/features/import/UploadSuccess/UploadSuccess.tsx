import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import styles from "./UploadSuccess.module.css";

interface UploadSuccessProps {
	fileName: string;
	contentId: string;
	onImportAnother: () => void;
}

export function UploadSuccess({
	fileName,
	contentId,
	onImportAnother,
}: UploadSuccessProps) {
	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.title}>Upload complete</p>
				<Badge variant="success">Completed</Badge>
			</div>
			<p className={styles.fileName}>{fileName}</p>
			<p className={styles.message}>
				Content created successfully. ID:{" "}
				<span className={styles.contentId}>{contentId}</span>
			</p>
			<p className={styles.hint}>
				The PDF file was not uploaded yet. Your content will appear in the
				library.
			</p>
			<div className={styles.action}>
				<Button variant="secondary" onClick={onImportAnother}>
					Import another PDF
				</Button>
			</div>
		</Card>
	);
}
