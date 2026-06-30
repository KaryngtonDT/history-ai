export interface ExecutionVersion {
	versionNumber: number;
	pipelineConfigurationId: string;
	optimizationId: string;
	qualityReportId: string;
	renderedVideoId: string;
	createdAt: string;
	optimizationProfile: string;
	qualityScore: number;
}

export interface ExecutionHistory {
	id: string;
	videoId: string;
	versions: ExecutionVersion[];
}

export interface ProviderDifference {
	stage: string;
	leftProvider: string;
	rightProvider: string;
}

export interface OptimizationDifference {
	leftProfile: string;
	rightProfile: string;
	changedParameters: string[];
}

export interface QualityScoreDifference {
	leftScore: number;
	rightScore: number;
	delta: number;
}

export interface ComparisonResult {
	leftVersion: number;
	rightVersion: number;
	providerDifferences: ProviderDifference[];
	optimizationDifference: OptimizationDifference | null;
	qualityScoreDifference: QualityScoreDifference | null;
}

export interface ReprocessExecutionInput {
	providerOverrides?: Record<string, string>;
	batchJobId?: string | null;
}

export interface ExecutionHistoryApiDto {
	id: string;
	videoId: string;
	versions: ExecutionVersionApiDto[];
}

export interface ExecutionVersionApiDto {
	versionNumber: number;
	pipelineConfigurationId: string;
	optimizationId: string;
	qualityReportId: string;
	renderedVideoId: string;
	createdAt: string;
	optimizationProfile: string;
	qualityScore: number;
}

export interface ComparisonResultApiDto {
	leftVersion: number;
	rightVersion: number;
	providerDifferences: ProviderDifference[];
	optimizationDifference: OptimizationDifference | null;
	qualityScoreDifference: QualityScoreDifference | null;
}

export const OPTIMIZATION_PROFILE_LABELS: Record<string, string> = {
	balanced: "Balanced",
	quality: "Quality",
	speed: "Speed",
	low_memory: "Low Memory",
};

export function mapExecutionHistoryFromApi(
	dto: ExecutionHistoryApiDto,
): ExecutionHistory {
	return {
		id: dto.id,
		videoId: dto.videoId,
		versions: dto.versions.map((version) => ({ ...version })),
	};
}

export function mapComparisonResultFromApi(
	dto: ComparisonResultApiDto,
): ComparisonResult {
	return {
		leftVersion: dto.leftVersion,
		rightVersion: dto.rightVersion,
		providerDifferences: dto.providerDifferences.map((entry) => ({ ...entry })),
		optimizationDifference: dto.optimizationDifference
			? { ...dto.optimizationDifference }
			: null,
		qualityScoreDifference: dto.qualityScoreDifference
			? { ...dto.qualityScoreDifference }
			: null,
	};
}
