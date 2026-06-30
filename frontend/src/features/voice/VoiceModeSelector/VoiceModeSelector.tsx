import type { VoiceMode } from "@/services/voice/types";
import styles from "./VoiceModeSelector.module.css";

interface VoiceModeSelectorProps {
	value: VoiceMode;
	onChange: (mode: VoiceMode) => void;
}

export function VoiceModeSelector({ value, onChange }: VoiceModeSelectorProps) {
	return (
		<fieldset className={styles.fieldset}>
			<legend className={styles.legend}>Voice Mode</legend>
			<label className={styles.option}>
				<input
					type="radio"
					name="voice-mode"
					value="generic"
					checked={value === "generic"}
					onChange={() => onChange("generic")}
				/>
				Generic Voice
			</label>
			<label className={styles.option}>
				<input
					type="radio"
					name="voice-mode"
					value="clone"
					checked={value === "clone"}
					onChange={() => onChange("clone")}
				/>
				Clone Original Voice
			</label>
		</fieldset>
	);
}
