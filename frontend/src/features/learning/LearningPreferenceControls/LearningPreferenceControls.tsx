import { useTranslation } from "@/i18n";
import styles from "../learning.module.css";

interface LearningPreferenceControlsProps {
	enabled: boolean;
	onToggle: (enabled: boolean) => Promise<void>;
	disabled?: boolean;
}

export function LearningPreferenceControls({
	enabled,
	onToggle,
	disabled = false,
}: LearningPreferenceControlsProps) {
	const { t } = useTranslation();

	return (
		<section
			className={styles.card}
			aria-labelledby="learning-preferences-heading"
		>
			<div className={styles.toggleRow}>
				<div>
					<h2 id="learning-preferences-heading" className={styles.title}>
						{t("learning.adaptive.toggleLabel")}
					</h2>
					<p className={styles.description}>
						{t("learning.adaptive.toggleDescription")}
					</p>
				</div>
				<label htmlFor="learning-adaptive-toggle">
					<input
						id="learning-adaptive-toggle"
						type="checkbox"
						checked={enabled}
						disabled={disabled}
						onChange={(event) => void onToggle(event.target.checked)}
					/>
				</label>
			</div>
		</section>
	);
}
