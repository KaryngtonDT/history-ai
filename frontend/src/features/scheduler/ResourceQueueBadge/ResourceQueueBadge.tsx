import { schedulerService } from "@/services/scheduler/SchedulerService";
import type { ExecutionResource } from "@/services/scheduler/types";
import styles from "./ResourceQueueBadge.module.css";

interface ResourceQueueBadgeProps {
	resource: ExecutionResource;
}

export function ResourceQueueBadge({ resource }: ResourceQueueBadgeProps) {
	return (
		<div className={styles.badge}>
			<p className={styles.label}>
				{schedulerService.formatResourceType(resource.type)} Queue
			</p>
			<p className={styles.value}>
				{schedulerService.formatQueueSummary(resource)}
			</p>
		</div>
	);
}
