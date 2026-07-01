import type { TelemetryRepository } from "./TelemetryRepository";
import { createTelemetryRepository } from "./TelemetryRepositoryFactory";
import type {
	PipelineTelemetry,
	ProviderStat,
	WorkspaceAnalytics,
} from "./types";

export class TelemetryService {
	private readonly repository: TelemetryRepository;

	constructor(repository: TelemetryRepository) {
		this.repository = repository;
	}

	loadAnalytics(workspaceId: string): Promise<WorkspaceAnalytics> {
		return this.repository.loadAnalytics(workspaceId);
	}

	loadProviderStatistics(workspaceId: string) {
		return this.repository.loadProviderStatistics(workspaceId);
	}

	loadTelemetry(workspaceId: string): Promise<PipelineTelemetry[]> {
		return this.repository.loadTelemetry(workspaceId);
	}

	formatProviderLabel(provider: ProviderStat): string {
		return `${provider.providerId} (${provider.stage.replaceAll("_", " ")})`;
	}

	qualityTrend(records: PipelineTelemetry[]): number[] {
		return records
			.map((record) => record.qualityScore)
			.filter((score): score is number => score !== null);
	}
}

export const telemetryService = new TelemetryService(
	createTelemetryRepository(),
);
