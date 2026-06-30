import type { VoiceOption } from "@/services/audio/types";
import styles from "./VoiceSelector.module.css";

interface VoiceSelectorProps {
	voices: VoiceOption[];
	value: string;
	onChange: (voiceId: string) => void;
}

export function VoiceSelector({ voices, value, onChange }: VoiceSelectorProps) {
	return (
		<select
			id="audio-voice"
			className={styles.select}
			value={value}
			onChange={(event) => onChange(event.target.value)}
		>
			{voices.map((voice) => (
				<option key={voice.voiceId} value={voice.voiceId}>
					{voice.displayName}
				</option>
			))}
		</select>
	);
}
