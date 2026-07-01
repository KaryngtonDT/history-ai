import { useTranslation } from "@/i18n/useTranslation";
import type { ShadowSpeechLanguage } from "../shadowVoice";
import {
	isSpeechSynthesisSupported,
	isVoiceAvailableForLanguage,
} from "../shadowVoice";
import styles from "./ShadowVoiceSettings.module.css";

interface ShadowVoiceSettingsProps {
	selectedLanguage: ShadowSpeechLanguage;
	disabled?: boolean;
	onChange: (language: ShadowSpeechLanguage) => void;
}

export function ShadowVoiceSettings({
	selectedLanguage,
	disabled = false,
	onChange,
}: ShadowVoiceSettingsProps) {
	const { t } = useTranslation();
	const synthesisSupported = isSpeechSynthesisSupported();
	const activeLanguage = selectedLanguage === "auto" ? "en" : selectedLanguage;
	const voiceMissing =
		synthesisSupported && !isVoiceAvailableForLanguage(activeLanguage);

	return (
		<section
			className={styles.voiceSettings}
			aria-label={t("pipeline.shadow.voiceSettingsTitle")}
		>
			<div className={styles.header}>
				<h2 className={styles.title}>
					{t("pipeline.shadow.voiceSettingsTitle")}
				</h2>
			</div>

			<div className={styles.field}>
				<label className={styles.label} htmlFor="shadow-speaking-language">
					{t("pipeline.shadow.speakingLanguage")}
				</label>
				<select
					id="shadow-speaking-language"
					className={styles.select}
					value={selectedLanguage}
					disabled={disabled}
					onChange={(event) =>
						onChange(event.target.value as ShadowSpeechLanguage)
					}
				>
					<option value="auto">
						{t("pipeline.shadow.speakingLanguageAuto")}
					</option>
					<option value="en">
						{t("pipeline.shadow.speakingLanguageEnglish")}
					</option>
					<option value="fr">
						{t("pipeline.shadow.speakingLanguageFrench")}
					</option>
					<option value="de">
						{t("pipeline.shadow.speakingLanguageGerman")}
					</option>
				</select>
			</div>

			{!synthesisSupported ? (
				<p className={styles.warning}>
					{t("pipeline.shadow.speechOutputUnavailable")}
				</p>
			) : null}

			{voiceMissing ? (
				<p className={styles.warning}>{t("pipeline.shadow.voiceMissing")}</p>
			) : null}
		</section>
	);
}
