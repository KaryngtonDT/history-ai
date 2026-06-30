import { describe, expect, it, vi } from "vitest";
import type { HistoryRepository } from "./HistoryRepository";
import { HistoryService } from "./HistoryService";
import { MOCK_PREVIEW_HISTORY } from "./MockHistoryRepository";

function createRepositoryMock(
	overrides: Partial<HistoryRepository> = {},
): HistoryRepository {
	return {
		getHistory: vi.fn().mockResolvedValue(MOCK_PREVIEW_HISTORY),
		getVersion: vi.fn().mockResolvedValue(MOCK_PREVIEW_HISTORY.versions[0]),
		compareVersions: vi.fn().mockResolvedValue({
			leftVersion: 1,
			rightVersion: 2,
			providerDifferences: [],
			optimizationDifference: null,
			qualityScoreDifference: null,
		}),
		reprocessVersion: vi.fn().mockResolvedValue(undefined),
		...overrides,
	};
}

describe("HistoryService", () => {
	it("loads history from repository", async () => {
		const getHistory = vi.fn().mockResolvedValue(MOCK_PREVIEW_HISTORY);
		const service = new HistoryService(createRepositoryMock({ getHistory }));

		const history = await service.loadHistory(MOCK_PREVIEW_HISTORY.videoId);

		expect(getHistory).toHaveBeenCalledWith(MOCK_PREVIEW_HISTORY.videoId);
		expect(history.versions).toHaveLength(2);
	});

	it("formats profile labels and sorts versions", () => {
		const service = new HistoryService(createRepositoryMock());

		expect(service.formatProfile("quality")).toBe("Quality");
		expect(
			service.sortedVersions(MOCK_PREVIEW_HISTORY.versions)[0].versionNumber,
		).toBe(2);
		expect(service.canCompare(1, 2)).toBe(true);
	});
});
