import type {
	AddProjectVideoInput,
	BatchJob,
	BatchJobStatus,
	CreateProjectInput,
	ProcessProjectInput,
	Project,
} from "./types";
import { BATCH_STATUS_LABELS, LANGUAGE_LABELS } from "./types";
import type { WorkspaceRepository } from "./WorkspaceRepository";
import { createWorkspaceRepository } from "./WorkspaceRepositoryFactory";

const TERMINAL_BATCH_STATUSES: BatchJobStatus[] = [
	"completed",
	"partial_failure",
	"failed",
];

export class WorkspaceService {
	private readonly repository: WorkspaceRepository;

	constructor(repository: WorkspaceRepository) {
		this.repository = repository;
	}

	listProjects(): Promise<Project[]> {
		return this.repository.listProjects();
	}

	getProject(projectId: string): Promise<Project> {
		return this.repository.getProject(projectId);
	}

	createProject(input: CreateProjectInput): Promise<Project> {
		return this.repository.createProject(input);
	}

	renameProject(projectId: string, name: string): Promise<Project> {
		return this.repository.updateProject(projectId, { name });
	}

	deleteProject(projectId: string): Promise<void> {
		return this.repository.deleteProject(projectId);
	}

	addVideo(projectId: string, input: AddProjectVideoInput): Promise<Project> {
		return this.repository.addVideo(projectId, input);
	}

	removeVideo(projectId: string, videoId: string): Promise<Project> {
		return this.repository.removeVideo(projectId, videoId);
	}

	processProject(
		projectId: string,
		input: ProcessProjectInput,
	): Promise<BatchJob> {
		return this.repository.processProject(projectId, input);
	}

	formatLanguage(language: string): string {
		return LANGUAGE_LABELS[language] ?? language;
	}

	formatBatchStatus(status: BatchJobStatus | null): string {
		if (!status) {
			return "Idle";
		}

		return BATCH_STATUS_LABELS[status] ?? status;
	}

	isBatchRunning(project: Project): boolean {
		return (
			project.batchStatus === "pending" || project.batchStatus === "running"
		);
	}

	isBatchTerminal(status: BatchJobStatus | null): boolean {
		return status !== null && TERMINAL_BATCH_STATUSES.includes(status);
	}

	processButtonLabel(videoCount: number): string {
		const label = videoCount === 1 ? "Video" : "Videos";

		return `Process ${videoCount} ${label}`;
	}

	canProcess(videoCount: number, selectedLanguages: string[]): boolean {
		return videoCount > 0 && selectedLanguages.length > 0;
	}
}

export const workspaceService = new WorkspaceService(
	createWorkspaceRepository(),
);
