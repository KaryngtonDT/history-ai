import type { WorkItem, WorkItemSummary } from "./types";
import type { WorkItemRepository } from "./WorkItemRepository";
import { createWorkItemRepository } from "./WorkItemRepositoryFactory";

export class WorkItemService {
	private readonly repository: WorkItemRepository;

	constructor(repository: WorkItemRepository) {
		this.repository = repository;
	}

	listRecentWork(limit?: number): Promise<WorkItem[]> {
		return this.repository.listRecentWork(limit);
	}

	getSummary(): Promise<WorkItemSummary> {
		return this.repository.getSummary();
	}

	formatTypeLabel(type: WorkItem["type"]): string {
		const labels: Record<WorkItem["type"], string> = {
			video: "Video",
			pdf: "PDF",
			audio: "Audio",
			youtube: "YouTube",
			project: "Project",
		};

		return labels[type];
	}

	formatStatusLabel(status: WorkItem["status"]): string {
		const labels: Record<WorkItem["status"], string> = {
			processing: "Processing",
			completed: "Completed",
			pending: "Pending",
			failed: "Failed",
			ready: "Ready",
		};

		return labels[status];
	}
}

export const workItemService = new WorkItemService(createWorkItemRepository());
