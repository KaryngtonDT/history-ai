import { useTranslation } from "@/i18n/useTranslation";
import type { VoiceMode } from "@/services/voice/types";
import styles from "./VoiceModeSelector.module.css";

interface VoiceModeSelectorProps {
	value: VoiceMode;
	onChange: (mode: VoiceMode) => void;
}

export function VoiceModeSelector({ value, onChange }: VoiceModeSelectorProps) {
	const { t } = useTranslation();

	return (
		<fieldset className={styles.fieldset}>
			<legend className={styles.legend}>
				{t("pipeline.voiceClone.modeTitle")}
			</legend>
			<label className={styles.option}>
				<input
					type="radio"
					name="voice-mode"
					value="generic"
					checked={value === "generic"}
					onChange={() => onChange("generic")}
				/>
				{t("pipeline.voiceClone.modeGeneric")}
			</label>
			<label className={styles.option}>
				<input
					type="radio"
					name="voice-mode"
					value="clone"
					checked={value === "clone"}
					onChange={() => onChange("clone")}
				/>
				{t("pipeline.voiceClone.modeClone")}
			</label>
		</fieldset>
	);
}
