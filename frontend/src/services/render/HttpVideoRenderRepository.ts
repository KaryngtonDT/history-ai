import { videoRenderListPath, videoRenderPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import {
	mapTranslationLanguage,
	type TranslationLanguage,
} from "@/services/translation/types";
import { ApiError } from "@/shared/errors";
import type {
	GenerateVideoRenderRequest,
	VideoRender,
	VideoRenderSummary,
} from "./types";
import {
	mapVideoRenderFormat,
	mapVideoRenderProvider,
	mapVideoRenderQuality,
} from "./types";
import type { VideoRenderRepository } from "./VideoRenderRepository";

interface VideoRenderApiDto {
	videoId: string;
	finalVideoId: string;
	targetLanguage: string;
	provider: string;
	format: string;
	quality: string;
	duration: number;
	fileSizeBytes: number;
	streamUrl: string;
	downloadUrl: string;
}

interface VideoRenderListApiDto {
	videoId: string;
	renders: Array<{
		videoId: string;
		finalVideoId: string;
		targetLanguage: string;
		provider: string;
		format: string;
		quality: string;
		duration: number;
		fileSizeBytes: number;
		streamUrl: string;
	}>;
}

function mapSummaryFromApi(
	dto: VideoRenderListApiDto["renders"][number],
): VideoRenderSummary {
	return {
		videoId: dto.videoId,
		finalVideoId: dto.finalVideoId,
		targetLanguage: mapTranslationLanguage(dto.targetLanguage),
		provider: mapVideoRenderProvider(dto.provider),
		format: mapVideoRenderFormat(dto.format),
		quality: mapVideoRenderQuality(dto.quality),
		duration: dto.duration,
		fileSizeBytes: dto.fileSizeBytes,
		streamUrl: dto.streamUrl,
	};
}

function mapRenderFromApi(dto: VideoRenderApiDto): VideoRender {
	return {
		...mapSummaryFromApi(dto),
		downloadUrl: dto.downloadUrl,
	};
}

export class HttpVideoRenderRepository implements VideoRenderRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listRenders(videoId: string): Promise<VideoRenderSummary[]> {
		try {
			const response = await this.httpClient.get<VideoRenderListApiDto>(
				videoRenderListPath(videoId),
			);

			return response.renders.map(mapSummaryFromApi);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return [];
			}

			throw error;
		}
	}

	async getRender(
		videoId: string,
		language: TranslationLanguage | string,
	): Promise<VideoRender | null> {
		try {
			const response = await this.httpClient.get<VideoRenderApiDto>(
				videoRenderPath(videoId, language),
			);

			return mapRenderFromApi(response);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return null;
			}

			throw error;
		}
	}

	async generateRender(
		videoId: string,
		request: GenerateVideoRenderRequest,
	): Promise<void> {
		await this.httpClient.post(videoRenderListPath(videoId), {
			targetLanguages: request.targetLanguages,
			provider: request.provider,
			format: request.format,
			quality: request.quality,
		});
	}
}
