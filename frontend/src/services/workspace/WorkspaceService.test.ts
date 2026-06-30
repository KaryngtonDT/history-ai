import { describe, expect, it, vi } from "vitest";
import { MOCK_PREVIEW_PROJECT } from "./MockWorkspaceRepository";
import type { WorkspaceRepository } from "./WorkspaceRepository";
import { WorkspaceService } from "./WorkspaceService";

function createRepositoryMock(
	overrides: Partial<WorkspaceRepository> = {},
): WorkspaceRepository {
	return {
		listProjects: vi.fn().mockResolvedValue([MOCK_PREVIEW_PROJECT]),
		getProject: vi.fn().mockResolvedValue(MOCK_PREVIEW_PROJECT),
		createProject: vi.fn().mockResolvedValue(MOCK_PREVIEW_PROJECT),
		updateProject: vi.fn().mockResolvedValue(MOCK_PREVIEW_PROJECT),
		deleteProject: vi.fn().mockResolvedValue(undefined),
		addVideo: vi.fn().mockResolvedValue(MOCK_PREVIEW_PROJECT),
		removeVideo: vi.fn().mockResolvedValue(MOCK_PREVIEW_PROJECT),
		processProject: vi.fn().mockResolvedValue({
			id: "batch-1",
			projectId: MOCK_PREVIEW_PROJECT.id,
			status: "running",
			progress: 0,
			totalVideos: 3,
			queuedVideos: 3,
			targetLanguages: ["fr", "de"],
			failedVideoIds: [],
		}),
		...overrides,
	};
}

describe("WorkspaceService", () => {
	it("loads projects from repository", async () => {
		const listProjects = vi.fn().mockResolvedValue([MOCK_PREVIEW_PROJECT]);
		const service = new WorkspaceService(
			createRepositoryMock({ listProjects }),
		);

		const projects = await service.listProjects();

		expect(listProjects).toHaveBeenCalledOnce();
		expect(projects).toEqual([MOCK_PREVIEW_PROJECT]);
	});

	it("starts batch processing through repository", async () => {
		const processProject = vi.fn().mockResolvedValue({
			id: "batch-1",
			projectId: MOCK_PREVIEW_PROJECT.id,
			status: "running",
			progress: 0,
			totalVideos: 3,
			queuedVideos: 3,
			targetLanguages: ["fr", "de"],
			failedVideoIds: [],
		});
		const service = new WorkspaceService(
			createRepositoryMock({ processProject }),
		);

		const batchJob = await service.processProject(MOCK_PREVIEW_PROJECT.id, {
			targetLanguages: ["fr", "de"],
		});

		expect(processProject).toHaveBeenCalledWith(MOCK_PREVIEW_PROJECT.id, {
			targetLanguages: ["fr", "de"],
		});
		expect(batchJob.status).toBe("running");
	});

	it("formats labels and process button text", () => {
		const service = new WorkspaceService(createRepositoryMock());

		expect(service.formatLanguage("fr")).toBe("French");
		expect(service.formatBatchStatus("running")).toBe("Running");
		expect(service.processButtonLabel(3)).toBe("Process 3 Videos");
		expect(service.canProcess(3, ["fr"])).toBe(true);
		expect(service.canProcess(0, ["fr"])).toBe(false);
	});

	it("detects running and terminal batch states", () => {
		const service = new WorkspaceService(createRepositoryMock());

		expect(
			service.isBatchRunning({
				...MOCK_PREVIEW_PROJECT,
				batchStatus: "running",
			}),
		).toBe(true);
		expect(service.isBatchTerminal("completed")).toBe(true);
		expect(service.isBatchTerminal("running")).toBe(false);
	});
});
