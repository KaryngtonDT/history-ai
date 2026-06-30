import type { ProcessingMode } from "@/services/orchestrator/types";
import { PROCESSING_MODE_LABELS } from "@/services/orchestrator/types";
import styles from "./ProcessingModeSelector.module.css";

interface ProcessingModeSelectorProps {
	mode: ProcessingMode;
	onChange: (mode: ProcessingMode) => void;
}

export function ProcessingModeSelector({
	mode,
	onChange,
}: ProcessingModeSelectorProps) {
	return (
		<div className={styles.processingModeSelector}>
			<p className={styles.title}>Processing Mode</p>
			<div className={styles.options}>
				<label className={styles.option}>
					<input
						type="radio"
						name="processingMode"
						value="manual"
						checked={mode === "manual"}
						onChange={() => onChange("manual")}
					/>
					{PROCESSING_MODE_LABELS.manual}
				</label>
				<label className={styles.option}>
					<input
						type="radio"
						name="processingMode"
						value="automatic"
						checked={mode === "automatic"}
						onChange={() => onChange("automatic")}
					/>
					{PROCESSING_MODE_LABELS.automatic}
					<span className={styles.recommended}>Recommended</span>
				</label>
			</div>
		</div>
	);
}
