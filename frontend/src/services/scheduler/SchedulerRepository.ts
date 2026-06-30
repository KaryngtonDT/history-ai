import type { ExecutionSchedule } from "./types";

export interface SchedulerRepository {
	getPreviewSchedule(): Promise<ExecutionSchedule>;
	getByVideoId(videoId: string): Promise<ExecutionSchedule>;
}
