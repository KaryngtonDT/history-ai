import { PROCESSING_JOBS_PATH } from "@/config/api";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import type { ProcessingRepository } from "./ProcessingRepository";
import type { ProcessingData, ProcessingJobApiDto } from "./types";
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
}
