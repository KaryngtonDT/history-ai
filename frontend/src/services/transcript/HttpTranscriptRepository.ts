import { videoTranscriptPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type {
	TranscriptLoadResult,
	TranscriptRepository,
} from "./TranscriptRepository";
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
		const result = await this.loadTranscript(videoId);

		return result.transcript;
	}

	async loadTranscript(videoId: string): Promise<TranscriptLoadResult> {
		try {
			const dto = await this.httpClient.get<VideoTranscriptApiDto>(
				videoTranscriptPath(videoId),
			);

			return {
				transcript: mapVideoTranscriptFromApi(dto),
			};
		} catch (error) {
			if (error instanceof ApiError) {
				return {
					transcript: null,
					unavailableDetail:
						error.body ??
						({
							status: error.status,
							message: error.message,
						} as const),
				};
			}

			throw error;
		}
	}
}
