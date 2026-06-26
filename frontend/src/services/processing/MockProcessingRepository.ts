import { processingMocks } from "@/mock/processing";
import type {
	CreateProcessingJobResult,
	ProcessingJobType,
	ProcessingRepository,
} from "./ProcessingRepository";
import type { ProcessingData } from "./types";

export class MockProcessingRepository implements ProcessingRepository {
	async getProcessing(id: string): Promise<ProcessingData | null> {
		return processingMocks[id] ?? null;
	}

	async createProcessingJob(
		_contentId: string,
		_type: ProcessingJobType,
	): Promise<CreateProcessingJobResult> {
		return {
			id: "1",
			status: "pending",
			progress: 0,
		};
	}
}
