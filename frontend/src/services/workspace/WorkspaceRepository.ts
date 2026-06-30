import type {
	AddProjectVideoInput,
	BatchJob,
	CreateProjectInput,
	ProcessProjectInput,
	Project,
	UpdateProjectInput,
} from "./types";

export interface WorkspaceRepository {
	listProjects(): Promise<Project[]>;
	getProject(projectId: string): Promise<Project>;
	createProject(input: CreateProjectInput): Promise<Project>;
	updateProject(projectId: string, input: UpdateProjectInput): Promise<Project>;
	deleteProject(projectId: string): Promise<void>;
	addVideo(projectId: string, input: AddProjectVideoInput): Promise<Project>;
	removeVideo(projectId: string, videoId: string): Promise<Project>;
	processProject(
		projectId: string,
		input: ProcessProjectInput,
	): Promise<BatchJob>;
}
