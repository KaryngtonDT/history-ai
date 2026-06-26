import { Card } from "@/components/ui/Card";
import type { ProcessingStepState } from "@/services/processing/types";
import { ProcessingStep } from "../ProcessingStep";
import styles from "./ProcessingTimeline.module.css";

interface ProcessingTimelineProps {
	steps: ProcessingStepState[];
}

export function ProcessingTimeline({ steps }: ProcessingTimelineProps) {
	return (
		<Card className={styles.card}>
			<h3 className={styles.heading}>Pipeline</h3>
			<ul className={styles.list}>
				{steps.map((step) => (
					<ProcessingStep key={step.label} step={step} />
				))}
			</ul>
		</Card>
	);
}
