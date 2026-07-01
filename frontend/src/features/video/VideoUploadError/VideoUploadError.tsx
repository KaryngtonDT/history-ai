import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n/useTranslation";
import styles from "./VideoUploadError.module.css";

interface VideoUploadErrorProps {
	message: string;
	onTryAgain: () => void;
}

export function VideoUploadError({
	message,
	onTryAgain,
}: VideoUploadErrorProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.title}>{t("pipeline.upload.videoUploadFailed")}</p>
				<Badge variant="danger">{t("common.error")}</Badge>
			</div>
			<p className={styles.message}>{message}</p>
			<div className={styles.action}>
				<Button variant="secondary" onClick={onTryAgain}>
					{t("pipeline.upload.tryAgain")}
				</Button>
			</div>
		</Card>
	);
}
