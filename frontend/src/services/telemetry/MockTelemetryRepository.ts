import type { TelemetryRepository } from "./TelemetryRepository";
import type {
	PipelineTelemetry,
	ProviderStatistics,
	WorkspaceAnalytics,
} from "./types";

const MOCK_ANALYTICS: WorkspaceAnalytics = {
	processedVideos: 328,
	averageProcessingTimeSeconds: 292,
	averageProcessingTimeLabel: "4m 52s",
	averageQuality: 94,
	successRate: 99.3,
	gpuUsagePercent: 71,
	topTranslationProvider: "Ollama",
	topTtsProvider: "F5-TTS",
	recentErrors: [
		{
			message: "LipSync timeout",
			status: "Resolved",
			recordedAt: "2026-06-26T12:00:00+00:00",
		},
		{
			message: "Translation retry",
			status: "Recovered",
			recordedAt: "2026-06-25T18:30:00+00:00",
		},
	],
};

const MOCK_PROVIDERS: ProviderStatistics = {
	providers: [
		{
			stage: "translation",
			providerId: "ollama",
			invocationCount: 210,
			averageDurationSeconds: 45.2,
		},
		{
			stage: "text_to_speech",
			providerId: "f5_tts",
			invocationCount: 198,
			averageDurationSeconds: 30.5,
		},
	],
};

const MOCK_TELEMETRY: PipelineTelemetry[] = [
	{
		id: "550e8400-e29b-41d4-a716-446655490101",
		workspaceId: "550e8400-e29b-41d4-a716-446655450001",
		videoId: "550e8400-e29b-41d4-a716-446655490010",
		success: true,
		metrics: [
			{ type: "processing_time", value: 292, unit: "seconds" },
			{ type: "gpu_usage", value: 71, unit: "percent" },
		],
		providerUsages: [],
		recordedAt: "2026-06-26T10:00:00+00:00",
		batchJobId: null,
		qualityScore: 94,
		errorMessage: null,
	},
];

export class MockTelemetryRepository implements TelemetryRepository {
	loadAnalytics(_workspaceId: string): Promise<WorkspaceAnalytics> {
		return Promise.resolve(MOCK_ANALYTICS);
	}

	loadProviderStatistics(_workspaceId: string): Promise<ProviderStatistics> {
		return Promise.resolve(MOCK_PROVIDERS);
	}

	loadTelemetry(_workspaceId: string): Promise<PipelineTelemetry[]> {
		return Promise.resolve(MOCK_TELEMETRY);
	}
}
