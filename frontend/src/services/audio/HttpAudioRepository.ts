import { videoAudioListPath, videoAudioPath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { mapTranslationLanguage } from "@/services/translation/types";
import { ApiError } from "@/shared/errors";
import type { AudioRepository } from "./AudioRepository";
import type {
	GenerateAudioRequest,
	VideoAudio,
	VideoAudioSummary,
} from "./types";
import { mapTextToSpeechProvider } from "./types";

interface VideoAudioApiDto {
	videoId: string;
	audioId: string;
	translationId: string;
	targetLanguage: string;
	provider: string;
	voiceId: string;
	voiceDisplayName: string;
	voiceLanguage: string;
	voiceGender: string;
	duration: number;
	format: string;
	downloadUrl: string;
}

interface VideoAudioListApiDto {
	videoId: string;
	audio: Array<{
		videoId: string;
		audioId: string;
		translationId: string;
		targetLanguage: string;
		provider: string;
		voiceId: string;
		voiceDisplayName: string;
		duration: number;
		format: string;
	}>;
}

function mapSummaryFromApi(
	dto: VideoAudioListApiDto["audio"][number],
): VideoAudioSummary {
	return {
		videoId: dto.videoId,
		audioId: dto.audioId,
		translationId: dto.translationId,
		targetLanguage: mapTranslationLanguage(dto.targetLanguage),
		provider: mapTextToSpeechProvider(dto.provider),
		voiceId: dto.voiceId,
		voiceDisplayName: dto.voiceDisplayName,
		duration: dto.duration,
		format: dto.format,
	};
}

function mapAudioFromApi(dto: VideoAudioApiDto): VideoAudio {
	return {
		...mapSummaryFromApi(dto),
		voiceLanguage: dto.voiceLanguage,
		voiceGender: dto.voiceGender as VideoAudio["voiceGender"],
		downloadUrl: dto.downloadUrl,
	};
}

export class HttpAudioRepository implements AudioRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listAudio(videoId: string): Promise<VideoAudioSummary[]> {
		try {
			const response = await this.httpClient.get<VideoAudioListApiDto>(
				videoAudioListPath(videoId),
			);

			return response.audio.map(mapSummaryFromApi);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return [];
			}

			throw error;
		}
	}

	async getAudio(
		videoId: string,
		language: string,
	): Promise<VideoAudio | null> {
		try {
			const response = await this.httpClient.get<VideoAudioApiDto>(
				videoAudioPath(videoId, language),
			);

			return mapAudioFromApi(response);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return null;
			}

			throw error;
		}
	}

	async generateAudio(
		videoId: string,
		request: GenerateAudioRequest,
	): Promise<void> {
		await this.httpClient.post(videoAudioListPath(videoId), {
			targetLanguages: request.targetLanguages,
			provider: request.provider,
			voiceId: request.voiceId,
		});
	}
}
