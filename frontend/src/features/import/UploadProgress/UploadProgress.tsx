import { Badge } from "@/components/ui/Badge";
import { Card } from "@/components/ui/Card";
import { Progress } from "@/components/ui/Progress";
import { Spinner } from "@/components/ui/Spinner";
import { useTranslation } from "@/i18n";
import styles from "./UploadProgress.module.css";

interface UploadProgressProps {
	fileName: string;
	progress: number;
}

export function UploadProgress({ fileName, progress }: UploadProgressProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.label}>{t("workspace.import.uploading")}</p>
				<Badge variant="info">{t("workspace.import.processing")}</Badge>
			</div>
			<p className={styles.fileName}>{fileName}</p>
			<div className={styles.progressBlock}>
				<Spinner label={t("workspace.import.uploadingFile")} />
				<Progress value={progress} />
			</div>
			<p className={styles.percent}>{progress} %</p>
		</Card>
	);
}
