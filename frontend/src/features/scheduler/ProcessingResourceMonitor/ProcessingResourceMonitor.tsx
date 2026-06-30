import { schedulerService } from "@/services/scheduler/SchedulerService";
import type { ExecutionSchedule } from "@/services/scheduler/types";
import { ResourceQueueBadge } from "../ResourceQueueBadge";
import { StageProgressTimeline } from "../StageProgressTimeline";
import styles from "./ProcessingResourceMonitor.module.css";

interface ProcessingResourceMonitorProps {
	schedule: ExecutionSchedule | null;
	loading?: boolean;
}

export function ProcessingResourceMonitor({
	schedule,
	loading = false,
}: ProcessingResourceMonitorProps) {
	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>Building execution schedule...</p>
			</div>
		);
	}

	if (!schedule) {
		return (
			<div className={styles.panel}>
				<p className={styles.fallback}>
					Schedule preview unavailable. Processing will continue sequentially.
				</p>
			</div>
		);
	}

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>Processing Resources</p>
				<span className={styles.badge}>Scheduler</span>
			</div>

			<div className={styles.summary}>
				<div>
					<p className={styles.label}>Resource mode</p>
					<p className={styles.value}>
						{schedulerService.formatStrategy(schedule.strategy)}
					</p>
				</div>
				<div>
					<p className={styles.label}>Estimated completion</p>
					<p className={styles.value}>
						{schedulerService.formatEstimatedCompletion(
							schedule.estimatedCompletionSeconds,
						)}
					</p>
				</div>
				{schedule.currentStage ? (
					<div>
						<p className={styles.label}>Current stage</p>
						<p className={styles.value}>
							{schedulerService.formatStageLabel(schedule.currentStage)}
						</p>
					</div>
				) : null}
			</div>

			<div className={styles.queues}>
				{schedule.resources.map((resource) => (
					<ResourceQueueBadge key={resource.type} resource={resource} />
				))}
			</div>

			<StageProgressTimeline stages={schedule.stages} />
		</div>
	);
}
