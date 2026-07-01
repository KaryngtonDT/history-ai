import { contentMock } from "@/mock/content";
import { MOCK_PREVIEW_PROJECT } from "@/services/workspace/MockWorkspaceRepository";
import type { WorkItem, WorkItemSummary } from "./types";
import type { WorkItemRepository } from "./WorkItemRepository";
import {
	mapContentToWorkItem,
	mapProjectToWorkItem,
	mapVideoToWorkItem,
} from "./workItemMappers";

function buildMockWorkItems(): WorkItem[] {
	const contentItems = contentMock.contents.map(mapContentToWorkItem);
	const projectItem = mapProjectToWorkItem(MOCK_PREVIEW_PROJECT);
	const videoItems = MOCK_PREVIEW_PROJECT.videos.map((video) =>
		mapVideoToWorkItem(
			video.videoId,
			video.filename,
			video.addedAt,
			MOCK_PREVIEW_PROJECT.batchProgress,
			MOCK_PREVIEW_PROJECT.batchStatus,
		),
	);

	return [...contentItems, projectItem, ...videoItems].sort((left, right) =>
		right.updatedAt.localeCompare(left.updatedAt),
	);
}

export class MockWorkItemRepository implements WorkItemRepository {
	async listRecentWork(limit = 10): Promise<WorkItem[]> {
		return buildMockWorkItems().slice(0, limit);
	}

	async getSummary(): Promise<WorkItemSummary> {
		const items = buildMockWorkItems();
		const continueWork =
			items.find((item) => item.status === "processing") ?? null;

		return {
			recentWork: items.slice(0, 8),
			continueWork,
			videoCount: items.filter((item) => item.type === "video").length,
			projectCount: items.filter((item) => item.type === "project").length,
			completedCount: items.filter((item) => item.status === "completed")
				.length,
			artifactCount: 12,
		};
	}
}
