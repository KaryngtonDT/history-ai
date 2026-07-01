import { useTranslation } from "@/i18n/useTranslation";
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
	const { t } = useTranslation();

	if (loading) {
		return (
			<div className={styles.panel}>
				<p className={styles.loading}>{t("pipeline.scheduler.loading")}</p>
			</div>
		);
	}

	if (!schedule) {
		return (
			<div className={styles.panel}>
				<p className={styles.fallback}>{t("pipeline.scheduler.fallback")}</p>
			</div>
		);
	}

	return (
		<div className={styles.panel}>
			<div className={styles.header}>
				<p className={styles.title}>{t("pipeline.scheduler.title")}</p>
				<span className={styles.badge}>{t("pipeline.scheduler.badge")}</span>
			</div>

			<div className={styles.summary}>
				<div>
					<p className={styles.label}>{t("pipeline.scheduler.resourceMode")}</p>
					<p className={styles.value}>
						{schedulerService.formatStrategy(schedule.strategy)}
					</p>
				</div>
				<div>
					<p className={styles.label}>
						{t("pipeline.scheduler.estimatedCompletion")}
					</p>
					<p className={styles.value}>
						{schedulerService.formatEstimatedCompletion(
							schedule.estimatedCompletionSeconds,
						)}
					</p>
				</div>
				{schedule.currentStage ? (
					<div>
						<p className={styles.label}>
							{t("pipeline.scheduler.currentStage")}
						</p>
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
