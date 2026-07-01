import { useTranslation } from "@/i18n/useTranslation";
import { schedulerService } from "@/services/scheduler/SchedulerService";
import type { ExecutionResource } from "@/services/scheduler/types";
import styles from "./ResourceQueueBadge.module.css";

interface ResourceQueueBadgeProps {
	resource: ExecutionResource;
}

export function ResourceQueueBadge({ resource }: ResourceQueueBadgeProps) {
	const { t } = useTranslation();

	return (
		<div className={styles.badge}>
			<p className={styles.label}>
				{schedulerService.formatResourceType(resource.type)}{" "}
				{t("pipeline.scheduler.queueSuffix")}
			</p>
			<p className={styles.value}>
				{schedulerService.formatQueueSummary(resource)}
			</p>
		</div>
	);
}
