import {
	videoHistoryComparePath,
	videoHistoryPath,
	videoHistoryReprocessPath,
	videoHistoryVersionPath,
} from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import type { HistoryRepository } from "./HistoryRepository";
import type {
	ComparisonResult,
	ComparisonResultApiDto,
	ExecutionHistory,
	ExecutionHistoryApiDto,
	ExecutionVersion,
	ExecutionVersionApiDto,
	ReprocessExecutionInput,
} from "./types";
import {
	mapComparisonResultFromApi,
	mapExecutionHistoryFromApi,
} from "./types";

export class HttpHistoryRepository implements HistoryRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getHistory(videoId: string): Promise<ExecutionHistory> {
		const response = await this.httpClient.get<ExecutionHistoryApiDto>(
			videoHistoryPath(videoId),
		);

		return mapExecutionHistoryFromApi(response);
	}

	async getVersion(
		videoId: string,
		version: number,
	): Promise<ExecutionVersion> {
		return this.httpClient.get<ExecutionVersionApiDto>(
			videoHistoryVersionPath(videoId, version),
		);
	}

	async compareVersions(
		videoId: string,
		leftVersion: number,
		rightVersion: number,
	): Promise<ComparisonResult> {
		const response = await this.httpClient.get<ComparisonResultApiDto>(
			`${videoHistoryComparePath(videoId)}?left=${leftVersion}&right=${rightVersion}`,
		);

		return mapComparisonResultFromApi(response);
	}

	async reprocessVersion(
		videoId: string,
		version: number,
		input: ReprocessExecutionInput = {},
	): Promise<void> {
		await this.httpClient.post(
			videoHistoryReprocessPath(videoId, version),
			input,
		);
	}
}
