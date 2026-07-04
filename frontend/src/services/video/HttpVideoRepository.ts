import { videoProcessPath, videoStatusPath, VIDEOS_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError, ValidationError } from "@/shared/errors";
import {
	mapVideoUploadFromApi,
	type VideoJobStatus,
	type VideoProcessResult,
	type VideoUploadApiDto,
	type VideoUploadOptions,
	type VideoUploadResult,
} from "./types";
import type { VideoRepository } from "./VideoRepository";

const INVALID_UPLOAD_MESSAGE =
	"Could not upload the video. Check the file format and try again.";

export class HttpVideoRepository implements VideoRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async uploadVideo(
		file: File,
		options?: VideoUploadOptions,
	): Promise<VideoUploadResult> {
		const formData = new FormData();
		formData.append("video", file);

		if (options?.processingMode) {
			formData.append("processingMode", options.processingMode);
		}
		if (options?.strategy) {
			formData.append("strategy", options.strategy);
		}

		try {
			const dto = await this.httpClient.postFormData<VideoUploadApiDto>(
				VIDEOS_PATH,
				formData,
				{ onProgress: options?.onProgress },
			);

			return mapVideoUploadFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 400) {
				throw new ValidationError(INVALID_UPLOAD_MESSAGE);
			}

			throw error;
		}
	}

	async getStatus(videoId: string): Promise<VideoJobStatus> {
		return this.httpClient.get<VideoJobStatus>(videoStatusPath(videoId));
	}

	async processVideo(videoId: string): Promise<VideoProcessResult> {
		return this.httpClient.post<VideoProcessResult>(
			videoProcessPath(videoId),
			{},
		);
	}
}
