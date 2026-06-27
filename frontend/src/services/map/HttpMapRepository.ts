import { timelineMapPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { MapRepository } from "./MapRepository";
import {
	type HistoricalPlace,
	mapTimelineMapFromApi,
	type TimelineMapApiDto,
} from "./types";

export class HttpMapRepository implements MapRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getTimelineMap(artifactId: string): Promise<HistoricalPlace[] | null> {
		try {
			const dto = await this.httpClient.get<TimelineMapApiDto>(
				timelineMapPath(artifactId),
			);

			return mapTimelineMapFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return null;
			}

			throw error;
		}
	}
}
