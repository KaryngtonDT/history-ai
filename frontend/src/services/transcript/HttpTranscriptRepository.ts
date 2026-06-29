import { videoTranscriptPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { TranscriptRepository } from "./TranscriptRepository";
import {
	mapVideoTranscriptFromApi,
	type VideoTranscript,
	type VideoTranscriptApiDto,
} from "./types";

export class HttpTranscriptRepository implements TranscriptRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getTranscript(videoId: string): Promise<VideoTranscript | null> {
		try {
			const dto = await this.httpClient.get<VideoTranscriptApiDto>(
				videoTranscriptPath(videoId),
			);

			return mapVideoTranscriptFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return null;
			}

			throw error;
		}
	}
}
