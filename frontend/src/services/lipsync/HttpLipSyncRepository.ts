import { videoLipSyncListPath, videoLipSyncPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import {
	mapTranslationLanguage,
	type TranslationLanguage,
} from "@/services/translation/types";
import { ApiError } from "@/shared/errors";
import type { LipSyncRepository } from "./LipSyncRepository";
import type {
	GenerateLipSyncRequest,
	VideoLipSync,
	VideoLipSyncSummary,
} from "./types";
import { mapLipSyncProvider } from "./types";

interface VideoLipSyncApiDto {
	videoId: string;
	artifactId: string;
	clonedAudioId: string;
	targetLanguage: string;
	provider: string;
	synchronizedVideoId: string;
	duration: number;
	originalVideoUrl: string;
	syncedVideoUrl: string;
}

interface VideoLipSyncListApiDto {
	videoId: string;
	lipSyncs: Array<{
		videoId: string;
		artifactId: string;
		clonedAudioId: string;
		targetLanguage: string;
		provider: string;
		synchronizedVideoId: string;
		duration: number;
		syncedVideoUrl: string;
	}>;
}

function mapSummaryFromApi(
	dto: VideoLipSyncListApiDto["lipSyncs"][number],
): VideoLipSyncSummary {
	return {
		videoId: dto.videoId,
		artifactId: dto.artifactId,
		clonedAudioId: dto.clonedAudioId,
		targetLanguage: mapTranslationLanguage(dto.targetLanguage),
		provider: mapLipSyncProvider(dto.provider),
		synchronizedVideoId: dto.synchronizedVideoId,
		duration: dto.duration,
		syncedVideoUrl: dto.syncedVideoUrl,
	};
}

function mapLipSyncFromApi(dto: VideoLipSyncApiDto): VideoLipSync {
	return {
		...mapSummaryFromApi(dto),
		originalVideoUrl: dto.originalVideoUrl,
	};
}

export class HttpLipSyncRepository implements LipSyncRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listLipSyncs(videoId: string): Promise<VideoLipSyncSummary[]> {
		try {
			const response = await this.httpClient.get<VideoLipSyncListApiDto>(
				videoLipSyncListPath(videoId),
			);

			return response.lipSyncs.map(mapSummaryFromApi);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return [];
			}

			throw error;
		}
	}

	async getLipSync(
		videoId: string,
		language: TranslationLanguage | string,
	): Promise<VideoLipSync | null> {
		try {
			const response = await this.httpClient.get<VideoLipSyncApiDto>(
				videoLipSyncPath(videoId, language),
			);

			return mapLipSyncFromApi(response);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return null;
			}

			throw error;
		}
	}

	async generateLipSync(
		videoId: string,
		request: GenerateLipSyncRequest,
	): Promise<void> {
		await this.httpClient.post(videoLipSyncListPath(videoId), {
			targetLanguages: request.targetLanguages,
			provider: request.provider,
		});
	}
}
