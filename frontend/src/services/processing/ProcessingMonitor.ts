import type { ProcessingData, ProcessingStatus } from "./types";

export type ProcessingUpdateCallback = (data: ProcessingData) => void;
export type ProcessingErrorCallback = (error: unknown) => void;

export interface ProcessingMonitor {
	subscribe(
		jobId: string,
		onUpdate: ProcessingUpdateCallback,
		onError?: ProcessingErrorCallback,
	): () => void;
}

export function isTerminalProcessingStatus(status: ProcessingStatus): boolean {
	return (
		status === "completed" || status === "failed" || status === "cancelled"
	);
}
