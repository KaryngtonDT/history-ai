import { processingMocks } from "@/mock/processing";
import type { ProcessingRepository } from "./ProcessingRepository";
import type { ProcessingData } from "./types";

export class MockProcessingRepository implements ProcessingRepository {
	getProcessing(id: string): ProcessingData | null {
		return processingMocks[id] ?? null;
	}
}
