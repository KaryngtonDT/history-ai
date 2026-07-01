export interface RecentTelemetryError {
	message: string;
	status: string;
	recordedAt: string;
}

export interface WorkspaceAnalytics {
	processedVideos: number;
	averageProcessingTimeSeconds: number;
	averageProcessingTimeLabel: string;
	averageQuality: number;
	successRate: number;
	gpuUsagePercent: number;
	topTranslationProvider: string;
	topTtsProvider: string;
	recentErrors: RecentTelemetryError[];
}

export interface ProviderStat {
	stage: string;
	providerId: string;
	invocationCount: number;
	averageDurationSeconds: number;
}

export interface ProviderStatistics {
	providers: ProviderStat[];
}

export interface ExecutionMetric {
	type: string;
	value: number;
	unit: string;
}

export interface ProviderUsage {
	stage: string;
	providerId: string;
	invocationCount: number;
	totalDurationSeconds: number;
}

export interface PipelineTelemetry {
	id: string;
	workspaceId: string;
	videoId: string;
	success: boolean;
	metrics: ExecutionMetric[];
	providerUsages: ProviderUsage[];
	recordedAt: string;
	batchJobId: string | null;
	qualityScore: number | null;
	errorMessage: string | null;
}

export interface WorkspaceAnalyticsApiDto {
	processedVideos: number;
	averageProcessingTimeSeconds: number;
	averageProcessingTimeLabel: string;
	averageQuality: number;
	successRate: number;
	gpuUsagePercent: number;
	topTranslationProvider: string;
	topTtsProvider: string;
	recentErrors: RecentTelemetryError[];
}

export interface ProviderStatisticsApiDto {
	providers: ProviderStat[];
}

export interface PipelineTelemetryApiDto {
	id: string;
	workspaceId: string;
	videoId: string;
	success: boolean;
	metrics: ExecutionMetric[];
	providerUsages: ProviderUsage[];
	recordedAt: string;
	batchJobId: string | null;
	qualityScore: number | null;
	errorMessage: string | null;
}

export function mapAnalyticsFromApi(
	dto: WorkspaceAnalyticsApiDto,
): WorkspaceAnalytics {
	return { ...dto };
}

export function mapProviderStatisticsFromApi(
	dto: ProviderStatisticsApiDto,
): ProviderStatistics {
	return { ...dto };
}

export function mapTelemetryFromApi(
	dto: PipelineTelemetryApiDto,
): PipelineTelemetry {
	return { ...dto };
}
