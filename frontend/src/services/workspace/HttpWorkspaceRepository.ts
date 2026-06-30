import {
	PROJECTS_PATH,
	projectPath,
	projectProcessPath,
	projectVideoPath,
	projectVideosPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type {
	AddProjectVideoInput,
	BatchJob,
	BatchJobApiDto,
	CreateProjectInput,
	ProcessProjectInput,
	Project,
	ProjectApiDto,
	UpdateProjectInput,
} from "./types";
import { mapBatchJobFromApi, mapProjectFromApi } from "./types";
import type { WorkspaceRepository } from "./WorkspaceRepository";

export class HttpWorkspaceRepository implements WorkspaceRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listProjects(): Promise<Project[]> {
		const projects = await this.httpClient.get<ProjectApiDto[]>(PROJECTS_PATH);

		return projects.map(mapProjectFromApi);
	}

	async getProject(projectId: string): Promise<Project> {
		const project = await this.httpClient.get<ProjectApiDto>(
			projectPath(projectId),
		);

		return mapProjectFromApi(project);
	}

	async createProject(input: CreateProjectInput): Promise<Project> {
		const project = await this.httpClient.post<ProjectApiDto>(
			PROJECTS_PATH,
			input,
		);

		return mapProjectFromApi(project);
	}

	async updateProject(
		projectId: string,
		input: UpdateProjectInput,
	): Promise<Project> {
		const project = await this.httpClient.patch<ProjectApiDto>(
			projectPath(projectId),
			input,
		);

		return mapProjectFromApi(project);
	}

	async deleteProject(projectId: string): Promise<void> {
		await this.httpClient.delete(projectPath(projectId));
	}

	async addVideo(
		projectId: string,
		input: AddProjectVideoInput,
	): Promise<Project> {
		const project = await this.httpClient.post<ProjectApiDto>(
			projectVideosPath(projectId),
			{ videoId: input.videoId },
		);

		return mapProjectFromApi(project);
	}

	async removeVideo(projectId: string, videoId: string): Promise<Project> {
		const project = await this.httpClient.deleteJson<ProjectApiDto>(
			projectVideoPath(projectId, videoId),
		);

		return mapProjectFromApi(project);
	}

	async processProject(
		projectId: string,
		input: ProcessProjectInput,
	): Promise<BatchJob> {
		const batchJob = await this.httpClient.post<BatchJobApiDto>(
			projectProcessPath(projectId),
			input,
		);

		return mapBatchJobFromApi(batchJob);
	}
}
