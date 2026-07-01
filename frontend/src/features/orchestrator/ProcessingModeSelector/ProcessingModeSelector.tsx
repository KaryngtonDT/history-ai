import { useTranslation } from "@/i18n/useTranslation";
import type { ProcessingMode } from "@/services/orchestrator/types";
import styles from "./ProcessingModeSelector.module.css";

interface ProcessingModeSelectorProps {
	mode: ProcessingMode;
	onChange: (mode: ProcessingMode) => void;
}

export function ProcessingModeSelector({
	mode,
	onChange,
}: ProcessingModeSelectorProps) {
	const { t } = useTranslation();

	return (
		<div className={styles.processingModeSelector}>
			<p className={styles.title}>{t("pipeline.mode.title")}</p>
			<div className={styles.options}>
				<label className={styles.option}>
					<input
						type="radio"
						name="processingMode"
						value="manual"
						checked={mode === "manual"}
						onChange={() => onChange("manual")}
					/>
					{t("pipeline.mode.manual")}
				</label>
				<label className={styles.option}>
					<input
						type="radio"
						name="processingMode"
						value="automatic"
						checked={mode === "automatic"}
						onChange={() => onChange("automatic")}
					/>
					{t("pipeline.mode.automatic")}
					<span className={styles.recommended}>
						{t("pipeline.mode.recommended")}
					</span>
				</label>
			</div>
		</div>
	);
}
