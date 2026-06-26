import { describe, expect, it, vi } from "vitest";
import { MockProcessingRepository } from "./MockProcessingRepository";
import { PollingProcessingMonitor } from "./PollingProcessingMonitor";
import type { ProcessingRepository } from "./ProcessingRepository";
import type { ProcessingData } from "./types";

function createRunningData(progress: number): ProcessingData {
	return {
		id: "job-1",
		title: "Summary processing",
		progress,
		status: "running",
		currentStep: "Processing",
		steps: [],
	};
}

function createRepositoryMock(
	getProcessing: ProcessingRepository["getProcessing"],
): ProcessingRepository {
	return {
		getProcessing,
		createProcessingJob: vi.fn(),
	};
}

describe("PollingProcessingMonitor", () => {
	it("polls immediately and on each interval", async () => {
		vi.useFakeTimers();

		const getProcessing = vi
			.fn<ProcessingRepository["getProcessing"]>()
			.mockResolvedValue(createRunningData(20));

		const monitor = new PollingProcessingMonitor(
			createRepositoryMock(getProcessing),
			2000,
		);
		const updates: number[] = [];

		monitor.subscribe("job-1", (data) => {
			updates.push(data.progress);
		});

		await vi.advanceTimersByTimeAsync(0);
		expect(updates).toEqual([20]);

		getProcessing.mockResolvedValue(createRunningData(45));
		await vi.advanceTimersByTimeAsync(2000);
		expect(updates).toEqual([20, 45]);

		vi.useRealTimers();
	});

	it("stops polling when status is completed", async () => {
		vi.useFakeTimers();

		const getProcessing = vi
			.fn<ProcessingRepository["getProcessing"]>()
			.mockResolvedValueOnce(createRunningData(80))
			.mockResolvedValueOnce({
				...createRunningData(100),
				status: "completed",
				currentStep: "Completed",
			});

		const monitor = new PollingProcessingMonitor(
			createRepositoryMock(getProcessing),
			2000,
		);
		const updates: ProcessingData["status"][] = [];

		monitor.subscribe("job-1", (data) => {
			updates.push(data.status);
		});

		await vi.advanceTimersByTimeAsync(0);
		await vi.advanceTimersByTimeAsync(2000);
		await vi.advanceTimersByTimeAsync(2000);

		expect(updates).toEqual(["running", "completed"]);
		expect(getProcessing).toHaveBeenCalledTimes(2);

		vi.useRealTimers();
	});

	it("stops polling when status is failed or cancelled", async () => {
		vi.useFakeTimers();

		for (const status of ["failed", "cancelled"] as const) {
			const getProcessing = vi
				.fn<ProcessingRepository["getProcessing"]>()
				.mockResolvedValue({
					...createRunningData(0),
					status,
				});

			const monitor = new PollingProcessingMonitor(
				createRepositoryMock(getProcessing),
				2000,
			);

			monitor.subscribe("job-1", () => {});

			await vi.advanceTimersByTimeAsync(0);
			await vi.advanceTimersByTimeAsync(4000);

			expect(getProcessing).toHaveBeenCalledTimes(1);
		}

		vi.useRealTimers();
	});

	it("stops polling when unsubscribed", async () => {
		vi.useFakeTimers();

		const getProcessing = vi
			.fn<ProcessingRepository["getProcessing"]>()
			.mockResolvedValue(createRunningData(20));

		const monitor = new PollingProcessingMonitor(
			createRepositoryMock(getProcessing),
			2000,
		);

		const unsubscribe = monitor.subscribe("job-1", () => {});

		await vi.advanceTimersByTimeAsync(0);
		unsubscribe();
		await vi.advanceTimersByTimeAsync(4000);

		expect(getProcessing).toHaveBeenCalledTimes(1);

		vi.useRealTimers();
	});

	it("works with MockProcessingRepository", async () => {
		const monitor = new PollingProcessingMonitor(
			new MockProcessingRepository(),
			2000,
		);

		const data = await new Promise<ProcessingData>((resolve) => {
			monitor.subscribe("1", (update) => {
				resolve(update);
			});
		});

		expect(data.title).toBe("The Roman Empire");
	});
});
