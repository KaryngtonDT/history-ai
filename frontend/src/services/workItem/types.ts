export type WorkItemType = "video" | "pdf" | "audio" | "youtube" | "project";

export type WorkItemStatus =
	| "processing"
	| "completed"
	| "pending"
	| "failed"
	| "ready";

export interface WorkItem {
	id: string;
	type: WorkItemType;
	title: string;
	status: WorkItemStatus;
	progress: number;
	currentStep: string;
	openRoute: string;
	primaryActionLabel: string;
	primaryActionRoute: string;
	icon: string;
	description: string;
	capabilities: string[];
	updatedAt: string;
}

export interface WorkItemSummary {
	recentWork: WorkItem[];
	continueWork: WorkItem | null;
	videoCount: number;
	projectCount: number;
	completedCount: number;
	artifactCount: number;
}
