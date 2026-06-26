import type { ProcessingData } from "./types";

export interface ProcessingRepository {
	getProcessing(id: string): Promise<ProcessingData | null>;
}
