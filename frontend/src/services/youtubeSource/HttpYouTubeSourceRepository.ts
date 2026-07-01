import { YOUTUBE_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError, ValidationError } from "@/shared/errors";
import type {
	YouTubeImport,
	YouTubeImportOptions,
	YouTubeImportResult,
	YouTubeMetadata,
} from "./types";
import type { YouTubeSourceRepository } from "./YouTubeSourceRepository";

const INVALID_IMPORT_MESSAGE =
	"Could not import the YouTube video. Check the URL and try again.";

export class HttpYouTubeSourceRepository implements YouTubeSourceRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async previewYouTube(url: string): Promise<YouTubeMetadata> {
		try {
			const response = await this.httpClient.post<{
				metadata: YouTubeMetadata;
			}>(`${YOUTUBE_PATH}/preview`, { url });
			return response.metadata;
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				throw new ValidationError(INVALID_IMPORT_MESSAGE);
			}

			throw error;
		}
	}

	async importYouTube(
		url: string,
		options?: YouTubeImportOptions,
	): Promise<YouTubeImportResult> {
		try {
			return await this.httpClient.post<YouTubeImportResult>(YOUTUBE_PATH, {
				url,
				processingMode: options?.processingMode,
				strategy: options?.strategy,
			});
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				throw new ValidationError(INVALID_IMPORT_MESSAGE);
			}

			throw error;
		}
	}

	async listYouTubeImports(): Promise<YouTubeImport[]> {
		return this.httpClient.get<YouTubeImport[]>(YOUTUBE_PATH);
	}

	async getYouTubeImport(youtubeId: string): Promise<YouTubeImport | null> {
		try {
			return await this.httpClient.get<YouTubeImport>(
				`${YOUTUBE_PATH}/${youtubeId}`,
			);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return null;
			}

			throw error;
		}
	}
}
