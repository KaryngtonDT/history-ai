import type { WorkItem, WorkItemSummary } from "./types";

export interface WorkItemRepository {
	listRecentWork(limit?: number): Promise<WorkItem[]>;
	getSummary(): Promise<WorkItemSummary>;
}
