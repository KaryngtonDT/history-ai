import { contentService } from "@/services/content/ContentService";
import { workspaceService } from "@/services/workspace/WorkspaceService";
import type { WorkItem, WorkItemSummary } from "./types";
import type { WorkItemRepository } from "./WorkItemRepository";
import {
	mapContentToWorkItem,
	mapProjectToWorkItem,
	mapVideoToWorkItem,
} from "./workItemMappers";

export class HttpWorkItemRepository implements WorkItemRepository {
	async listRecentWork(limit = 10): Promise<WorkItem[]> {
		const [contents, projects] = await Promise.all([
			contentService.listContents(),
			workspaceService.listProjects(),
		]);

		const contentItems = contents.map(mapContentToWorkItem);
		const projectItems = projects.flatMap((project) => {
			const projectItem = mapProjectToWorkItem(project);
			const videoItems = project.videos.map((video) =>
				mapVideoToWorkItem(
					video.videoId,
					video.filename,
					video.addedAt,
					project.batchProgress,
					project.batchStatus,
				),
			);

			return [projectItem, ...videoItems];
		});

		return [...contentItems, ...projectItems]
			.sort((left, right) => right.updatedAt.localeCompare(left.updatedAt))
			.slice(0, limit);
	}

	async getSummary(): Promise<WorkItemSummary> {
		const recentWork = await this.listRecentWork(8);
		const continueWork =
			recentWork.find((item) => item.status === "processing") ?? null;

		return {
			recentWork,
			continueWork,
			videoCount: recentWork.filter((item) => item.type === "video").length,
			projectCount: recentWork.filter((item) => item.type === "project").length,
			completedCount: recentWork.filter((item) => item.status === "completed")
				.length,
			artifactCount: 0,
		};
	}
}
