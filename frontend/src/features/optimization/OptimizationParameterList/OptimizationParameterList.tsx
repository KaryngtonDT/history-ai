import { optimizationService } from "@/services/optimization/OptimizationService";
import type { OptimizationStage } from "@/services/optimization/types";
import styles from "./OptimizationParameterList.module.css";

interface OptimizationParameterListProps {
	stages: OptimizationStage[];
}

export function OptimizationParameterList({
	stages,
}: OptimizationParameterListProps) {
	return (
		<div className={styles.root}>
			{stages.map((stage) => (
				<div key={stage.stage} className={styles.stage}>
					<p className={styles.stageTitle}>
						{optimizationService.formatStageLabel(stage.stage)}
					</p>
					<ul className={styles.parameterList}>
						{stage.parameters.map((parameter) => (
							<li key={`${stage.stage}-${parameter.key}`}>
								<span className={styles.label}>
									{optimizationService.formatParameterLabel(parameter.key)}
								</span>
								<span className={styles.value}>{parameter.value}</span>
							</li>
						))}
					</ul>
				</div>
			))}
		</div>
	);
}
