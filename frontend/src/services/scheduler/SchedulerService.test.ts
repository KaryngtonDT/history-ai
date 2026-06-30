import { describe, expect, it, vi } from "vitest";
import { MOCK_PREVIEW_SCHEDULE } from "./MockSchedulerRepository";
import type { SchedulerRepository } from "./SchedulerRepository";
import { SchedulerService } from "./SchedulerService";

function createRepositoryMock(
	overrides: Partial<SchedulerRepository> = {},
): SchedulerRepository {
	return {
		getPreviewSchedule: vi.fn().mockResolvedValue(MOCK_PREVIEW_SCHEDULE),
		getByVideoId: vi.fn().mockResolvedValue(MOCK_PREVIEW_SCHEDULE),
		...overrides,
	};
}

describe("SchedulerService", () => {
	it("loads preview schedule from repository", async () => {
		const getPreviewSchedule = vi.fn().mockResolvedValue(MOCK_PREVIEW_SCHEDULE);
		const service = new SchedulerService(
			createRepositoryMock({ getPreviewSchedule }),
		);

		const result = await service.loadPreviewSchedule();

		expect(getPreviewSchedule).toHaveBeenCalledOnce();
		expect(result).toEqual(MOCK_PREVIEW_SCHEDULE);
	});

	it("formats labels and queue summaries", () => {
		const service = new SchedulerService(createRepositoryMock());

		expect(service.formatStrategy("balanced")).toBe("Balanced");
		expect(service.formatResourceType("gpu")).toBe("GPU");
		expect(service.formatStageLabel("voice_clone")).toBe("Voice Clone");
		expect(service.formatEstimatedCompletion(360)).toBe("6 min");
		expect(service.formatQueueSummary(MOCK_PREVIEW_SCHEDULE.resources[1])).toBe(
			"1 running / 2 pending",
		);
	});
});
