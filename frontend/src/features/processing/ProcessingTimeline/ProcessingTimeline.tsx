import { Card } from "@/components/ui/Card";
import { useTranslation } from "@/i18n";
import type { ProcessingStepState } from "@/services/processing/types";
import { ProcessingStep } from "../ProcessingStep";
import styles from "./ProcessingTimeline.module.css";

interface ProcessingTimelineProps {
	steps: ProcessingStepState[];
}

export function ProcessingTimeline({ steps }: ProcessingTimelineProps) {
	const { t } = useTranslation();

	return (
		<Card className={styles.card}>
			<h3 className={styles.heading}>{t("workspace.processing.pipeline")}</h3>
			<ul className={styles.list}>
				{steps.map((step) => (
					<ProcessingStep key={step.label} step={step} />
				))}
			</ul>
		</Card>
	);
}
