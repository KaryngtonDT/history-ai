import { CONTENTS_PATH, PROCESSING_JOBS_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type {
	CreateProcessingJobResult,
	ProcessingJobType,
	ProcessingRepository,
} from "./ProcessingRepository";
import type {
	CreateProcessingJobApiResponseDto,
	ProcessingData,
	ProcessingJobApiDto,
} from "./types";
import { mapProcessingFromApi } from "./types";

export class HttpProcessingRepository implements ProcessingRepository {
	private readonly httpClient: HttpClient;

	constructor(httpClient: HttpClient) {
		this.httpClient = httpClient;
	}

	async getProcessing(id: string): Promise<ProcessingData | null> {
		try {
			const dto = await this.httpClient.get<ProcessingJobApiDto>(
				`${PROCESSING_JOBS_PATH}/${id}`,
			);

			return mapProcessingFromApi(dto);
		} catch (error) {
			if (error instanceof ApiError && error.status === 404) {
				return null;
			}

			throw error;
		}
	}

	async createProcessingJob(
		contentId: string,
		type: ProcessingJobType,
	): Promise<CreateProcessingJobResult> {
		const dto = await this.httpClient.post<CreateProcessingJobApiResponseDto>(
			`${CONTENTS_PATH}/${contentId}/processing-jobs`,
			{ type },
		);

		return {
			id: dto.id,
			status: mapProcessingFromApi({
				id: dto.id,
				contentId,
				type,
				status: dto.status,
				progress: dto.progress,
				startedAt: null,
				completedAt: null,
				failedAt: null,
			}).status,
			progress: dto.progress,
		};
	}
}
