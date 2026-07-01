import type {
	PipelineTelemetry,
	ProviderStatistics,
	WorkspaceAnalytics,
} from "./types";

export interface TelemetryRepository {
	loadAnalytics(workspaceId: string): Promise<WorkspaceAnalytics>;
	loadProviderStatistics(workspaceId: string): Promise<ProviderStatistics>;
	loadTelemetry(workspaceId: string): Promise<PipelineTelemetry[]>;
}
