import { Badge } from "@/components/ui/Badge";
import { Button } from "@/components/ui/Button";
import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n";
import styles from "./UploadError.module.css";

interface UploadErrorProps {
	message: string;
	onTryAgain: () => void;
}

export function UploadError({ message, onTryAgain }: UploadErrorProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<div className={styles.header}>
				<p className={styles.title}>{t("workspace.import.uploadFailed")}</p>
				<Badge variant="danger">{t("common.error")}</Badge>
			</div>
			<p className={styles.message}>{message}</p>
			<div className={styles.action}>
				<Button variant="secondary" onClick={onTryAgain}>
					{t("workspace.import.tryAgain")}
				</Button>
			</div>
		</Card>
	);
}
