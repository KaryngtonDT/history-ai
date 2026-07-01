import { AUDIO_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError, ValidationError } from "@/shared/errors";
import type { AudioSourceRepository } from "./AudioSourceRepository";
import {
	type AudioSource,
	type AudioSourceApiDto,
	type AudioUploadApiDto,
	type AudioUploadOptions,
	type AudioUploadResult,
	mapAudioSourceFromApi,
	mapAudioUploadFromApi,
} from "./types";

const INVALID_UPLOAD_MESSAGE =
	"Could not upload the audio file. Check the file format and try again.";

export class HttpAudioSourceRepository implements AudioSourceRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async listAudioSources(): Promise<AudioSource[]> {
		const items = await this.httpClient.get<AudioSourceApiDto[]>(AUDIO_PATH);
		return items.map(mapAudioSourceFromApi);
	}

	async getAudioSource(audioId: string): Promise<AudioSource | null> {
		try {
			const dto = await this.httpClient.get<AudioSourceApiDto>(
				`${AUDIO_PATH}/${audioId}`,
			);
			return mapAudioSourceFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return null;
			}

			throw error;
		}
	}

	async uploadAudio(
		file: File,
		options?: AudioUploadOptions,
	): Promise<AudioUploadResult> {
		const formData = new FormData();
		formData.append("audio", file);

		if (options?.processingMode) {
			formData.append("processingMode", options.processingMode);
		}
		if (options?.strategy) {
			formData.append("strategy", options.strategy);
		}

		try {
			const dto = await this.httpClient.postFormData<AudioUploadApiDto>(
				AUDIO_PATH,
				formData,
				{ onProgress: options?.onProgress },
			);

			return mapAudioUploadFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				throw new ValidationError(INVALID_UPLOAD_MESSAGE);
			}

			throw error;
		}
	}
}
