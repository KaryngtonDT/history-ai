import type { ProcessingData } from "./types";

export type ProcessingJobType = "summary";

export interface CreateProcessingJobResult {
	id: string;
	status: ProcessingData["status"];
	progress: number;
}

export interface ProcessingRepository {
	getProcessing(id: string): Promise<ProcessingData | null>;
	createProcessingJob(
		contentId: string,
		type: ProcessingJobType,
	): Promise<CreateProcessingJobResult>;
}
