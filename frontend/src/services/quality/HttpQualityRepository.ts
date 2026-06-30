import { videoQualityPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { MOCK_PREVIEW_QUALITY } from "./MockQualityRepository";
import type { QualityRepository } from "./QualityRepository";
import type { QualityReport } from "./types";

export class HttpQualityRepository implements QualityRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getPreviewQuality(): Promise<QualityReport> {
		return MOCK_PREVIEW_QUALITY;
	}

	async getByVideoId(videoId: string): Promise<QualityReport> {
		return this.httpClient.get<QualityReport>(videoQualityPath(videoId));
	}
}
