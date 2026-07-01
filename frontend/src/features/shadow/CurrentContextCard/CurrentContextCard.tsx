import { useTranslation } from "@/i18n/useTranslation";
import type { WatchContextSegment } from "@/services/shadow/types";
import styles from "./CurrentContextCard.module.css";

interface CurrentContextCardProps {
	currentTime: number;
	segment: WatchContextSegment | null;
}

export function CurrentContextCard({
	currentTime,
	segment,
}: CurrentContextCardProps) {
	const { t } = useTranslation();

	return (
		<div className={styles.card}>
			<p className={styles.label}>{t("pipeline.shadow.currentTime")}</p>
			<p className={styles.time}>{currentTime.toFixed(1)}s</p>
			{segment ? (
				<p className={styles.segment}>
					{t("pipeline.shadow.currentSegment", {
						index: segment.index,
						text: segment.text,
					})}
				</p>
			) : (
				<p className={styles.segment}>{t("pipeline.shadow.noSegment")}</p>
			)}
		</div>
	);
}
