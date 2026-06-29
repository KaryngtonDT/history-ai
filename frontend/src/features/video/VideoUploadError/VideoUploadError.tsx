import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import styles from "./VideoUploadError.module.css";

interface VideoUploadErrorProps {
	message: string;
	onTryAgain: () => void;
}

export function VideoUploadError({
	message,
	onTryAgain,
}: VideoUploadErrorProps) {
	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.title}>Upload failed</p>
				<Badge variant="danger">Error</Badge>
			</div>
			<p className={styles.message}>{message}</p>
			<div className={styles.action}>
				<Button variant="secondary" onClick={onTryAgain}>
					Try again
				</Button>
			</div>
		</Card>
	);
}
