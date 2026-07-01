import { useTranslation } from "@/i18n/useTranslation";
import type { ShadowInteraction } from "@/services/shadow/types";
import styles from "./ShadowTimeline.module.css";

interface ShadowTimelineProps {
	interactions: ShadowInteraction[];
}

function formatTimestamp(seconds: number): string {
	return `${seconds.toFixed(1)}s`;
}

export function ShadowTimeline({ interactions }: ShadowTimelineProps) {
	const { t } = useTranslation();

	if (interactions.length === 0) {
		return <p className={styles.empty}>{t("pipeline.shadow.timelineEmpty")}</p>;
	}

	return (
		<ul className={styles.list}>
			{interactions.map((interaction) => (
				<li
					key={`${interaction.kind}-${interaction.participant}-${interaction.videoTimestamp}-${interaction.text ?? ""}`}
					className={styles.item}
				>
					<span className={styles.meta}>
						{formatTimestamp(interaction.videoTimestamp)} ·{" "}
						{interaction.participant}
					</span>
					{interaction.text ? <p>{interaction.text}</p> : null}
				</li>
			))}
		</ul>
	);
}
