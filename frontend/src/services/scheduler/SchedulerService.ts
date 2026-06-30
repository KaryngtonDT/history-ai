import type { SchedulerRepository } from "./SchedulerRepository";
import { createSchedulerRepository } from "./SchedulerRepositoryFactory";
import type { ExecutionResource, ExecutionSchedule } from "./types";
import {
	RESOURCE_TYPE_LABELS,
	SCHEDULER_STAGE_LABELS,
	SCHEDULING_STRATEGY_LABELS,
	STAGE_STATUS_LABELS,
} from "./types";

export class SchedulerService {
	private readonly repository: SchedulerRepository;

	constructor(repository: SchedulerRepository) {
		this.repository = repository;
	}

	loadPreviewSchedule(): Promise<ExecutionSchedule> {
		return this.repository.getPreviewSchedule();
	}

	loadByVideoId(videoId: string): Promise<ExecutionSchedule> {
		return this.repository.getByVideoId(videoId);
	}

	formatStrategy(strategy: string): string {
		return SCHEDULING_STRATEGY_LABELS[strategy] ?? strategy;
	}

	formatResourceType(type: string): string {
		return RESOURCE_TYPE_LABELS[type] ?? type.toUpperCase();
	}

	formatStageLabel(stage: string): string {
		return SCHEDULER_STAGE_LABELS[stage] ?? stage;
	}

	formatStageStatus(status: string): string {
		return STAGE_STATUS_LABELS[status] ?? status;
	}

	formatEstimatedCompletion(seconds: number): string {
		const minutes = Math.max(1, Math.round(seconds / 60));
		return `${minutes} min`;
	}

	formatQueueSummary(resource: ExecutionResource): string {
		return `${resource.running} running / ${resource.pending} pending`;
	}
}

export const schedulerService = new SchedulerService(
	createSchedulerRepository(),
);
