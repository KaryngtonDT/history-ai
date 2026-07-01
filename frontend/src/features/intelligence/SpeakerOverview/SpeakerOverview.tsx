import { useTranslation } from "@/i18n/useTranslation";
import {
	EMOTION_LABELS,
	type VideoIntelligence,
} from "@/services/intelligence/types";
import styles from "./SpeakerOverview.module.css";

interface SpeakerOverviewProps {
	intelligence: VideoIntelligence;
}

export function SpeakerOverview({ intelligence }: SpeakerOverviewProps) {
	const { t } = useTranslation();

	return (
		<div className={styles.root}>
			<p className={styles.label}>{t("pipeline.intelligence.speakers")}</p>
			<p className={styles.value}>{intelligence.audio.speakerCount}</p>
			<ul className={styles.list}>
				{intelligence.speakers.map((speaker) => (
					<li key={speaker.index}>{speaker.label}</li>
				))}
			</ul>
			<p className={styles.meta}>
				{t("pipeline.intelligence.emotion")}:{" "}
				{EMOTION_LABELS[intelligence.speech.dominantEmotion] ??
					intelligence.speech.dominantEmotion}
			</p>
		</div>
	);
}
