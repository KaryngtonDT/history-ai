import { useTranslation } from "@/i18n/useTranslation";
import type { WatchContextSegment } from "@/services/shadow/types";
import styles from "./ShadowTranslationPanel.module.css";

interface ShadowTranslationPanelProps {
	segment: WatchContextSegment | null;
	nearbyContext: string;
	collapsed?: boolean;
	onToggle?: () => void;
}

export function ShadowTranslationPanel({
	segment,
	nearbyContext,
	collapsed = false,
	onToggle,
}: ShadowTranslationPanelProps) {
	const { t } = useTranslation();

	return (
		<section className={styles.panel}>
			<button type="button" className={styles.header} onClick={onToggle}>
				{t("pipeline.shadow.translationTitle")}
			</button>
			{!collapsed ? (
				<div className={styles.body}>
					{segment?.translatedText ? (
						<p className={styles.active}>{segment.translatedText}</p>
					) : (
						<p className={styles.muted}>{t("pipeline.shadow.noTranslation")}</p>
					)}
					{nearbyContext ? (
						<p className={styles.nearby}>{nearbyContext}</p>
					) : null}
				</div>
			) : null}
		</section>
	);
}
