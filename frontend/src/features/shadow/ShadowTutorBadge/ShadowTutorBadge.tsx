import { useTranslation } from "@/i18n/useTranslation";
import styles from "./ShadowTutorBadge.module.css";

interface ShadowTutorBadgeProps {
	enabled: boolean;
}

export function ShadowTutorBadge({ enabled }: ShadowTutorBadgeProps) {
	const { t } = useTranslation();

	return (
		<span className={enabled ? styles.badge : styles.badgeMuted}>
			{enabled
				? t("pipeline.shadow.tutorBadgeActive")
				: t("pipeline.shadow.tutorBadgeOff")}
		</span>
	);
}
