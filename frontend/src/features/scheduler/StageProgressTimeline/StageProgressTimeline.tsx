import { schedulerService } from "@/services/scheduler/SchedulerService";
import type { ScheduledStage } from "@/services/scheduler/types";
import styles from "./StageProgressTimeline.module.css";

interface StageProgressTimelineProps {
	stages: ScheduledStage[];
}

export function StageProgressTimeline({ stages }: StageProgressTimelineProps) {
	return (
		<ul className={styles.timeline}>
			{stages.map((stage) => (
				<li key={stage.stage} className={styles.item}>
					<span className={`${styles.status} ${styles[stage.status]}`}>
						{schedulerService.formatStageStatus(stage.status)}
					</span>
					<div>
						<p className={styles.stage}>
							{schedulerService.formatStageLabel(stage.stage)}
						</p>
						<p className={styles.meta}>
							{schedulerService.formatResourceType(
								stage.requirements[0]?.type ?? "cpu",
							)}{" "}
							· {stage.estimatedDurationSeconds}s
						</p>
					</div>
				</li>
			))}
		</ul>
	);
}
