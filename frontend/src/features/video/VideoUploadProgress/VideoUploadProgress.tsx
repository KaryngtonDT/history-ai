import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n/useTranslation";
import styles from "./VideoUploadProgress.module.css";

interface VideoUploadProgressProps {
	fileName: string;
	progress: number;
}

export function VideoUploadProgress({
	fileName,
	progress,
}: VideoUploadProgressProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>{t("pipeline.upload.videoUploading")}</p>
				<Badge variant="info">{t("pipeline.upload.videoInProgress")}</Badge>
			</div>
			<p className={styles.fileName}>{fileName}</p>
			<div className={styles.progressBlock}>
				<Spinner label={t("pipeline.upload.videoUploadingLabel")} />
				<Progress value={progress} />
			</div>
			<p className={styles.percent}>{progress} %</p>
		</Card>
	);
}
