import { timelinePath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { TimelineRepository } from "./TimelineRepository";
import {
	mapTimelineFromApi,
	type Timeline,
	type TimelineApiDto,
} from "./types";

export class HttpTimelineRepository implements TimelineRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getTimeline(artifactId: string): Promise<Timeline | null> {
		try {
			const dto = await this.httpClient.get<TimelineApiDto>(
				timelinePath(artifactId),
			);

			return mapTimelineFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return null;
			}

			throw error;
		}
	}
}
