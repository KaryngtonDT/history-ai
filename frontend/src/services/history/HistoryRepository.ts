import type {
	ComparisonResult,
	ExecutionHistory,
	ExecutionVersion,
	ReprocessExecutionInput,
} from "./types";

export interface HistoryRepository {
	getHistory(videoId: string): Promise<ExecutionHistory>;
	getVersion(videoId: string, version: number): Promise<ExecutionVersion>;
	compareVersions(
		videoId: string,
		leftVersion: number,
		rightVersion: number,
	): Promise<ComparisonResult>;
	reprocessVersion(
		videoId: string,
		version: number,
		input?: ReprocessExecutionInput,
	): Promise<void>;
}
