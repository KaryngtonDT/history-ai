import { videoIntelligencePath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { MOCK_PREVIEW_INTELLIGENCE } from "./MockVideoIntelligenceRepository";
import type { VideoIntelligence } from "./types";
import type { VideoIntelligenceRepository } from "./VideoIntelligenceRepository";

export class HttpVideoIntelligenceRepository
	implements VideoIntelligenceRepository
{
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getPreviewIntelligence(): Promise<VideoIntelligence> {
		return MOCK_PREVIEW_INTELLIGENCE;
	}

	async getByVideoId(videoId: string): Promise<VideoIntelligence> {
		return this.httpClient.get<VideoIntelligence>(
			videoIntelligencePath(videoId),
		);
	}
}
