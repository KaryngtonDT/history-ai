import { videoSchedulePath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { MOCK_PREVIEW_SCHEDULE } from "./MockSchedulerRepository";
import type { SchedulerRepository } from "./SchedulerRepository";
import type { ExecutionSchedule } from "./types";

export class HttpSchedulerRepository implements SchedulerRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getPreviewSchedule(): Promise<ExecutionSchedule> {
		return MOCK_PREVIEW_SCHEDULE;
	}

	async getByVideoId(videoId: string): Promise<ExecutionSchedule> {
		return this.httpClient.get<ExecutionSchedule>(videoSchedulePath(videoId));
	}
}
