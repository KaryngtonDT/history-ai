import { videoTranslationPath, videoTranslationsPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { TranslationRepository } from "./TranslationRepository";
import type {
	GenerateTranslationsRequest,
	VideoTranslation,
	VideoTranslationSummary,
} from "./types";
import { mapTranslationLanguage, mapTranslationProvider } from "./types";

interface VideoTranslationApiDto {
	videoId: string;
	translationId: string;
	sourceLanguage: string;
	targetLanguage: string;
	provider: string;
	text: string;
	segmentCount: number;
	segments: Array<{
		index: number;
		sourceText: string;
		translatedText: string;
	}>;
}

interface VideoTranslationsListApiDto {
	videoId: string;
	translations: Array<{
		videoId: string;
		translationId: string;
		sourceLanguage: string;
		targetLanguage: string;
		provider: string;
		text: string;
		segmentCount: number;
	}>;
}

function mapTranslationFromApi(dto: VideoTranslationApiDto): VideoTranslation {
	return {
		videoId: dto.videoId,
		translationId: dto.translationId,
		sourceLanguage: mapTranslationLanguage(dto.sourceLanguage),
		targetLanguage: mapTranslationLanguage(dto.targetLanguage),
		provider: mapTranslationProvider(dto.provider),
		text: dto.text,
		segmentCount: dto.segmentCount,
		segments: dto.segments.map((segment) => ({
			index: segment.index,
			sourceText: segment.sourceText,
			translatedText: segment.translatedText,
		})),
	};
}

function mapSummaryFromApi(
	dto: VideoTranslationsListApiDto["translations"][number],
): VideoTranslationSummary {
	return {
		videoId: dto.videoId,
		translationId: dto.translationId,
		sourceLanguage: mapTranslationLanguage(dto.sourceLanguage),
		targetLanguage: mapTranslationLanguage(dto.targetLanguage),
		provider: mapTranslationProvider(dto.provider),
		text: dto.text,
		segmentCount: dto.segmentCount,
	};
}

export class HttpTranslationRepository implements TranslationRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listTranslations(videoId: string): Promise<VideoTranslationSummary[]> {
		try {
			const response = await this.httpClient.get<VideoTranslationsListApiDto>(
				videoTranslationsPath(videoId),
			);

			return response.translations.map(mapSummaryFromApi);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return [];
			}

			throw error;
		}
	}

	async getTranslation(
		videoId: string,
		language: string,
	): Promise<VideoTranslation | null> {
		try {
			const response = await this.httpClient.get<VideoTranslationApiDto>(
				videoTranslationPath(videoId, language),
			);

			return mapTranslationFromApi(response);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return null;
			}

			throw error;
		}
	}

	async generateTranslations(
		videoId: string,
		request: GenerateTranslationsRequest,
	): Promise<void> {
		await this.httpClient.post(videoTranslationsPath(videoId), {
			targetLanguages: request.targetLanguages,
			provider: request.provider,
		});
	}
}
