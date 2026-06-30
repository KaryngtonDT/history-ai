import type { HistoryRepository } from "./HistoryRepository";
import { createHistoryRepository } from "./HistoryRepositoryFactory";
import type {
	ComparisonResult,
	ExecutionHistory,
	ExecutionVersion,
	ReprocessExecutionInput,
} from "./types";
import { OPTIMIZATION_PROFILE_LABELS } from "./types";

export class HistoryService {
	private readonly repository: HistoryRepository;

	constructor(repository: HistoryRepository) {
		this.repository = repository;
	}

	loadHistory(videoId: string): Promise<ExecutionHistory> {
		return this.repository.getHistory(videoId);
	}

	loadVersion(videoId: string, version: number): Promise<ExecutionVersion> {
		return this.repository.getVersion(videoId, version);
	}

	compareVersions(
		videoId: string,
		leftVersion: number,
		rightVersion: number,
	): Promise<ComparisonResult> {
		return this.repository.compareVersions(videoId, leftVersion, rightVersion);
	}

	reprocessVersion(
		videoId: string,
		version: number,
		input?: ReprocessExecutionInput,
	): Promise<void> {
		return this.repository.reprocessVersion(videoId, version, input);
	}

	formatProfile(profile: string): string {
		return OPTIMIZATION_PROFILE_LABELS[profile] ?? profile;
	}

	sortedVersions(versions: ExecutionVersion[]): ExecutionVersion[] {
		return [...versions].sort(
			(left, right) => right.versionNumber - left.versionNumber,
		);
	}

	canCompare(leftVersion: number | null, rightVersion: number | null): boolean {
		return (
			leftVersion !== null &&
			rightVersion !== null &&
			leftVersion !== rightVersion
		);
	}
}

export const historyService = new HistoryService(createHistoryRepository());
