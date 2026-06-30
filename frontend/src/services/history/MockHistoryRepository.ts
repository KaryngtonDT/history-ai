import type { HistoryRepository } from "./HistoryRepository";
import type {
	ComparisonResult,
	ExecutionHistory,
	ExecutionVersion,
	ReprocessExecutionInput,
} from "./types";

export const MOCK_PREVIEW_HISTORY: ExecutionHistory = {
	id: "550e8400-e29b-41d4-a716-446655460001",
	videoId: "550e8400-e29b-41d4-a716-446655450101",
	versions: [
		{
			versionNumber: 1,
			pipelineConfigurationId: "550e8400-e29b-41d4-a716-446655460101",
			optimizationId: "550e8400-e29b-41d4-a716-446655460102",
			qualityReportId: "550e8400-e29b-41d4-a716-446655460103",
			renderedVideoId: "550e8400-e29b-41d4-a716-446655460104",
			createdAt: "2026-06-25T10:00:00+00:00",
			optimizationProfile: "balanced",
			qualityScore: 91,
		},
		{
			versionNumber: 2,
			pipelineConfigurationId: "550e8400-e29b-41d4-a716-446655460201",
			optimizationId: "550e8400-e29b-41d4-a716-446655460202",
			qualityReportId: "550e8400-e29b-41d4-a716-446655460203",
			renderedVideoId: "550e8400-e29b-41d4-a716-446655460204",
			createdAt: "2026-06-26T10:00:00+00:00",
			optimizationProfile: "quality",
			qualityScore: 96,
		},
	],
};

export class MockHistoryRepository implements HistoryRepository {
	async getHistory(videoId: string): Promise<ExecutionHistory> {
		return {
			...MOCK_PREVIEW_HISTORY,
			videoId,
			versions: MOCK_PREVIEW_HISTORY.versions.map((version) => ({
				...version,
			})),
		};
	}

	async getVersion(
		videoId: string,
		version: number,
	): Promise<ExecutionVersion> {
		const history = await this.getHistory(videoId);
		const match = history.versions.find(
			(entry) => entry.versionNumber === version,
		);

		if (!match) {
			throw new Error("Execution version not found");
		}

		return { ...match };
	}

	async compareVersions(
		videoId: string,
		leftVersion: number,
		rightVersion: number,
	): Promise<ComparisonResult> {
		void videoId;

		return {
			leftVersion,
			rightVersion,
			providerDifferences: [
				{
					stage: "translation",
					leftProvider: "ollama",
					rightProvider: "mock",
				},
			],
			optimizationDifference: {
				leftProfile: "balanced",
				rightProfile: "quality",
				changedParameters: ["speech_to_text.beamSize"],
			},
			qualityScoreDifference: {
				leftScore: 91,
				rightScore: 96,
				delta: 5,
			},
		};
	}

	async reprocessVersion(
		videoId: string,
		version: number,
		input: ReprocessExecutionInput = {},
	): Promise<void> {
		void videoId;
		void version;
		void input;
	}
}
