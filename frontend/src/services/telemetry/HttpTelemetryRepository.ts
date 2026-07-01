import {
	workspaceAnalyticsPath,
	workspaceProvidersPath,
	workspaceTelemetryPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { TelemetryRepository } from "./TelemetryRepository";
import type {
	PipelineTelemetryApiDto,
	ProviderStatisticsApiDto,
	WorkspaceAnalyticsApiDto,
} from "./types";
import {
	mapAnalyticsFromApi,
	mapProviderStatisticsFromApi,
	mapTelemetryFromApi,
} from "./types";

export class HttpTelemetryRepository implements TelemetryRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async loadAnalytics(workspaceId: string) {
		const analytics = await this.httpClient.get<WorkspaceAnalyticsApiDto>(
			workspaceAnalyticsPath(workspaceId),
		);

		return mapAnalyticsFromApi(analytics);
	}

	async loadProviderStatistics(workspaceId: string) {
		const statistics = await this.httpClient.get<ProviderStatisticsApiDto>(
			workspaceProvidersPath(workspaceId),
		);

		return mapProviderStatisticsFromApi(statistics);
	}

	async loadTelemetry(workspaceId: string) {
		const records = await this.httpClient.get<PipelineTelemetryApiDto[]>(
			workspaceTelemetryPath(workspaceId),
		);

		return records.map(mapTelemetryFromApi);
	}
}
