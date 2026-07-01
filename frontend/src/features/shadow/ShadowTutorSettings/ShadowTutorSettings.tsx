import { useTranslation } from "@/i18n/useTranslation";
import type {
	ShadowChallengeLevel,
	ShadowExplanationStyle,
	ShadowInterventionFrequency,
	ShadowInterventionPolicy,
	ShadowTutorMode,
} from "@/services/shadow/types";
import { frequencyFromPolicy, tutorModeFromPolicy } from "../shadowTutorPolicy";
import styles from "./ShadowTutorSettings.module.css";

interface ShadowTutorSettingsProps {
	policy: ShadowInterventionPolicy;
	disabled?: boolean;
	onChange: (update: {
		tutorMode?: ShadowTutorMode;
		challengeLevel?: ShadowChallengeLevel;
		explanationStyle?: ShadowExplanationStyle;
		frequency?: ShadowInterventionFrequency;
		autoResume?: boolean;
		allowAutoPause?: boolean;
	}) => void;
}

export function ShadowTutorSettings({
	policy,
	disabled = false,
	onChange,
}: ShadowTutorSettingsProps) {
	const { t } = useTranslation();
	const tutorMode = tutorModeFromPolicy(policy);
	const frequency = frequencyFromPolicy(policy);

	return (
		<section
			className={styles.shadowTutor}
			aria-label={t("pipeline.shadow.tutorSettingsTitle")}
		>
			<div className={styles.header}>
				<h2 className={styles.title}>
					{t("pipeline.shadow.tutorSettingsTitle")}
				</h2>
			</div>

			<div className={styles.toggleRow}>
				<label className={styles.label} htmlFor="shadow-proactive-mode">
					{t("pipeline.shadow.proactiveMode")}
				</label>
				<input
					id="shadow-proactive-mode"
					type="checkbox"
					checked={policy.enabled}
					disabled={disabled}
					onChange={(event) =>
						onChange({
							tutorMode: event.target.checked ? "gentle" : "off",
						})
					}
				/>
			</div>

			{policy.enabled ? (
				<div className={styles.grid}>
					<div className={styles.field}>
						<label className={styles.label} htmlFor="shadow-tutor-mode">
							{t("pipeline.shadow.tutorMode")}
						</label>
						<select
							id="shadow-tutor-mode"
							className={styles.select}
							value={tutorMode === "off" ? "gentle" : tutorMode}
							disabled={disabled}
							onChange={(event) =>
								onChange({
									tutorMode: event.target.value as ShadowTutorMode,
								})
							}
						>
							<option value="gentle">
								{t("pipeline.shadow.tutorModeGentle")}
							</option>
							<option value="normal">
								{t("pipeline.shadow.tutorModeNormal")}
							</option>
						</select>
					</div>

					<div className={styles.field}>
						<label className={styles.label} htmlFor="shadow-challenge-level">
							{t("pipeline.shadow.challengeLevel")}
						</label>
						<select
							id="shadow-challenge-level"
							className={styles.select}
							value={policy.challengeLevel}
							disabled={disabled}
							onChange={(event) =>
								onChange({
									challengeLevel: event.target.value as ShadowChallengeLevel,
								})
							}
						>
							<option value="easy">{t("pipeline.shadow.challengeEasy")}</option>
							<option value="normal">
								{t("pipeline.shadow.challengeNormal")}
							</option>
							<option value="hard">{t("pipeline.shadow.challengeHard")}</option>
						</select>
					</div>

					<div className={styles.field}>
						<label className={styles.label} htmlFor="shadow-frequency">
							{t("pipeline.shadow.interventionFrequency")}
						</label>
						<select
							id="shadow-frequency"
							className={styles.select}
							value={frequency}
							disabled={disabled}
							onChange={(event) =>
								onChange({
									frequency: event.target.value as ShadowInterventionFrequency,
								})
							}
						>
							<option value="low">{t("pipeline.shadow.frequencyLow")}</option>
							<option value="normal">
								{t("pipeline.shadow.frequencyNormal")}
							</option>
							<option value="high">{t("pipeline.shadow.frequencyHigh")}</option>
						</select>
					</div>

					<div className={styles.field}>
						<label className={styles.label} htmlFor="shadow-explanation-style">
							{t("pipeline.shadow.explanationStyle")}
						</label>
						<select
							id="shadow-explanation-style"
							className={styles.select}
							value={policy.explanationStyle}
							disabled={disabled}
							onChange={(event) =>
								onChange({
									explanationStyle: event.target
										.value as ShadowExplanationStyle,
								})
							}
						>
							<option value="short">
								{t("pipeline.shadow.explanationShort")}
							</option>
							<option value="detailed">
								{t("pipeline.shadow.explanationDetailed")}
							</option>
							<option value="example_first">
								{t("pipeline.shadow.explanationExampleFirst")}
							</option>
						</select>
					</div>

					<label className={styles.checkboxRow}>
						<input
							type="checkbox"
							checked={policy.allowAutoPause}
							disabled={disabled}
							onChange={(event) =>
								onChange({ allowAutoPause: event.target.checked })
							}
						/>
						{t("pipeline.shadow.autoPause")}
					</label>

					<label className={styles.checkboxRow}>
						<input
							type="checkbox"
							checked={policy.autoResume}
							disabled={disabled}
							onChange={(event) =>
								onChange({ autoResume: event.target.checked })
							}
						/>
						{t("pipeline.shadow.autoResume")}
					</label>
				</div>
			) : null}
		</section>
	);
}
