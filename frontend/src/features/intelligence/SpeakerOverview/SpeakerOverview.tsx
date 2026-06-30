import {
	EMOTION_LABELS,
	type VideoIntelligence,
} from "@/services/intelligence/types";
import styles from "./SpeakerOverview.module.css";

interface SpeakerOverviewProps {
	intelligence: VideoIntelligence;
}

export function SpeakerOverview({ intelligence }: SpeakerOverviewProps) {
	return (
		<div className={styles.root}>
			<p className={styles.label}>Speakers</p>
			<p className={styles.value}>{intelligence.audio.speakerCount}</p>
			<ul className={styles.list}>
				{intelligence.speakers.map((speaker) => (
					<li key={speaker.index}>{speaker.label}</li>
				))}
			</ul>
			<p className={styles.meta}>
				Emotion:{" "}
				{EMOTION_LABELS[intelligence.speech.dominantEmotion] ??
					intelligence.speech.dominantEmotion}
			</p>
		</div>
	);
}
