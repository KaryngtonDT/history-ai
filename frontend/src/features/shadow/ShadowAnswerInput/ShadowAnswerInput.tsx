import { useTranslation } from "@/i18n/useTranslation";
import { ShadowVoiceButton } from "../ShadowVoiceButton";
import styles from "./ShadowAnswerInput.module.css";

interface ShadowAnswerInputProps {
	value: string;
	disabled?: boolean;
	onChange: (value: string) => void;
	onSubmit: () => void;
	onSkip: () => void;
}

export function ShadowAnswerInput({
	value,
	disabled = false,
	onChange,
	onSubmit,
	onSkip,
}: ShadowAnswerInputProps) {
	const { t } = useTranslation();

	return (
		<form
			className={styles.form}
			onSubmit={(event) => {
				event.preventDefault();
				onSubmit();
			}}
		>
			<label className={styles.voice} htmlFor="shadow-intervention-answer">
				{t("pipeline.shadow.answerLabel")}
			</label>
			<textarea
				id="shadow-intervention-answer"
				className={styles.input}
				rows={3}
				value={value}
				disabled={disabled}
				placeholder={t("pipeline.shadow.answerPlaceholder")}
				onChange={(event) => onChange(event.target.value)}
			/>
			<ShadowVoiceButton onTranscript={onChange} />
			<div className={styles.actions}>
				<button type="submit" className={styles.primary} disabled={disabled}>
					{t("pipeline.shadow.submitAnswer")}
				</button>
				<button
					type="button"
					className={styles.secondary}
					disabled={disabled}
					onClick={onSkip}
				>
					{t("pipeline.shadow.skipIntervention")}
				</button>
			</div>
		</form>
	);
}
