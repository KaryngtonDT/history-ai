import type { ProcessingStepState } from "@/services/processing/types";
import styles from "./ProcessingStep.module.css";

interface ProcessingStepProps {
	step: ProcessingStepState;
}

export function ProcessingStep({ step }: ProcessingStepProps) {
	const icon = step.completed ? "✓" : step.active ? "⟳" : "○";

	return (
		<li
			className={styles.step}
			data-completed={step.completed}
			data-active={step.active}
		>
			<span className={styles.icon} aria-hidden="true">
				{icon}
			</span>
			<span className={styles.label}>{step.label}</span>
		</li>
	);
}
