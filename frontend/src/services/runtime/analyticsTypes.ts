export interface RuntimeEngineAnalytics {
	engineId: string;
	executionCount: number;
	completedCount: number;
	failedCount: number;
	successRate: number | null;
	failureRate: number | null;
	averageDurationSeconds: number | null;
	medianDurationSeconds: number | null;
	fastestDurationSeconds: number | null;
	slowestDurationSeconds: number | null;
	averageEstimationErrorSeconds: number | null;
	relativeSpeedScore: number | null;
	relativeSpeedLabel: string | null;
	hardwareProfiles: string[];
}
