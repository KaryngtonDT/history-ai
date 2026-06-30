import type {
	AddProjectVideoInput,
	BatchJob,
	CreateProjectInput,
	ProcessProjectInput,
	Project,
	UpdateProjectInput,
} from "./types";
import type { WorkspaceRepository } from "./WorkspaceRepository";

export const MOCK_PREVIEW_PROJECT: Project = {
	id: "550e8400-e29b-41d4-a716-446655450001",
	name: "Marketing Campaign",
	createdAt: "2026-06-01T10:00:00+00:00",
	videos: [
		{
			videoId: "550e8400-e29b-41d4-a716-446655450101",
			filename: "Interview.mp4",
			addedAt: "2026-06-01T10:05:00+00:00",
		},
		{
			videoId: "550e8400-e29b-41d4-a716-446655450102",
			filename: "Podcast.mp4",
			addedAt: "2026-06-01T10:06:00+00:00",
		},
		{
			videoId: "550e8400-e29b-41d4-a716-446655450103",
			filename: "Demo.mp4",
			addedAt: "2026-06-01T10:07:00+00:00",
		},
	],
	batchJobId: "550e8400-e29b-41d4-a716-446655450201",
	batchStatus: "running",
	batchProgress: 63,
	targetLanguages: ["fr", "de"],
};

const projects: Project[] = [
	{ ...MOCK_PREVIEW_PROJECT, videos: [...MOCK_PREVIEW_PROJECT.videos] },
];

function cloneProject(project: Project): Project {
	return {
		...project,
		videos: project.videos.map((video) => ({ ...video })),
		targetLanguages: [...project.targetLanguages],
	};
}

export class MockWorkspaceRepository implements WorkspaceRepository {
	async listProjects(): Promise<Project[]> {
		return projects.map(cloneProject);
	}

	async getProject(projectId: string): Promise<Project> {
		const project = projects.find((entry) => entry.id === projectId);

		if (!project) {
			throw new Error("Project not found");
		}

		return cloneProject(project);
	}

	async createProject(input: CreateProjectInput): Promise<Project> {
		const project: Project = {
			id: `550e8400-e29b-41d4-a716-44665545${String(projects.length + 1).padStart(4, "0")}`,
			name: input.name,
			createdAt: new Date().toISOString(),
			videos: [],
			batchJobId: null,
			batchStatus: null,
			batchProgress: 0,
			targetLanguages: [],
		};

		projects.unshift(project);

		return cloneProject(project);
	}

	async updateProject(
		projectId: string,
		input: UpdateProjectInput,
	): Promise<Project> {
		const project = projects.find((entry) => entry.id === projectId);

		if (!project) {
			throw new Error("Project not found");
		}

		project.name = input.name;

		return cloneProject(project);
	}

	async deleteProject(projectId: string): Promise<void> {
		const index = projects.findIndex((entry) => entry.id === projectId);

		if (index === -1) {
			throw new Error("Project not found");
		}

		projects.splice(index, 1);
	}

	async addVideo(
		projectId: string,
		input: AddProjectVideoInput,
	): Promise<Project> {
		const project = projects.find((entry) => entry.id === projectId);

		if (!project) {
			throw new Error("Project not found");
		}

		project.videos.push({
			videoId: input.videoId,
			filename: input.filename ?? `${input.videoId}.mp4`,
			addedAt: new Date().toISOString(),
		});

		return cloneProject(project);
	}

	async removeVideo(projectId: string, videoId: string): Promise<Project> {
		const project = projects.find((entry) => entry.id === projectId);

		if (!project) {
			throw new Error("Project not found");
		}

		project.videos = project.videos.filter(
			(video) => video.videoId !== videoId,
		);

		return cloneProject(project);
	}

	async processProject(
		projectId: string,
		input: ProcessProjectInput,
	): Promise<BatchJob> {
		const project = projects.find((entry) => entry.id === projectId);

		if (!project) {
			throw new Error("Project not found");
		}

		const batchJob: BatchJob = {
			id: `550e8400-e29b-41d4-a716-44665545${String(projects.length + 900).padStart(4, "0")}`,
			projectId,
			status: "running",
			progress: 0,
			totalVideos: project.videos.length,
			queuedVideos: project.videos.length,
			targetLanguages: [...input.targetLanguages],
			failedVideoIds: [],
		};

		project.batchJobId = batchJob.id;
		project.batchStatus = batchJob.status;
		project.batchProgress = batchJob.progress;
		project.targetLanguages = [...input.targetLanguages];

		return { ...batchJob, failedVideoIds: [...batchJob.failedVideoIds] };
	}
}
