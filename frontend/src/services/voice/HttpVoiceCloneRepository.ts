import { videoVoiceCloneListPath, videoVoiceClonePath } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { mapTranslationLanguage } from "@/services/translation/types";
import { ApiError } from "@/shared/errors";
import type {
	GenerateVoiceCloneRequest,
	VideoVoiceClone,
	VideoVoiceCloneSummary,
} from "./types";
import { mapVoiceCloneProvider } from "./types";
import type { VoiceCloneRepository } from "./VoiceCloneRepository";

interface VideoVoiceCloneApiDto {
	videoId: string;
	artifactId: string;
	sourceAudioId: string;
	clonedAudioId: string;
	targetLanguage: string;
	provider: string;
	sourceLanguage: string;
	duration: number;
	sampleRate: number;
	originalAudioUrl: string;
	clonedAudioUrl: string;
}

interface VideoVoiceCloneListApiDto {
	videoId: string;
	voiceClones: Array<{
		videoId: string;
		artifactId: string;
		sourceAudioId: string;
		clonedAudioId: string;
		targetLanguage: string;
		provider: string;
		duration: number;
		sampleRate: number;
	}>;
}

function mapSummaryFromApi(
	dto: VideoVoiceCloneListApiDto["voiceClones"][number],
): VideoVoiceCloneSummary {
	return {
		videoId: dto.videoId,
		artifactId: dto.artifactId,
		sourceAudioId: dto.sourceAudioId,
		clonedAudioId: dto.clonedAudioId,
		targetLanguage: mapTranslationLanguage(dto.targetLanguage),
		provider: mapVoiceCloneProvider(dto.provider),
		duration: dto.duration,
		sampleRate: dto.sampleRate,
	};
}

function mapVoiceCloneFromApi(dto: VideoVoiceCloneApiDto): VideoVoiceClone {
	return {
		...mapSummaryFromApi(dto),
		sourceLanguage: mapTranslationLanguage(dto.sourceLanguage),
		originalAudioUrl: dto.originalAudioUrl,
		clonedAudioUrl: dto.clonedAudioUrl,
	};
}

export class HttpVoiceCloneRepository implements VoiceCloneRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listVoiceClones(videoId: string): Promise<VideoVoiceCloneSummary[]> {
		try {
			const response = await this.httpClient.get<VideoVoiceCloneListApiDto>(
				videoVoiceCloneListPath(videoId),
			);

			return response.voiceClones.map(mapSummaryFromApi);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return [];
			}

			throw error;
		}
	}

	async getVoiceClone(
		videoId: string,
		language: string,
	): Promise<VideoVoiceClone | null> {
		try {
			const response = await this.httpClient.get<VideoVoiceCloneApiDto>(
				videoVoiceClonePath(videoId, language),
			);

			return mapVoiceCloneFromApi(response);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				return null;
			}

			throw error;
		}
	}

	async generateVoiceClone(
		videoId: string,
		request: GenerateVoiceCloneRequest,
	): Promise<void> {
		await this.httpClient.post(videoVoiceCloneListPath(videoId), {
			targetLanguages: request.targetLanguages,
			provider: request.provider,
			voiceMode: request.voiceMode,
		});
	}
}
