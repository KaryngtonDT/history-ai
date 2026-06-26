import { describe, expect, it, vi } from "vitest";
import { MockProcessingRepository } from "./MockProcessingRepository";
import { ProcessingService } from "./ProcessingService";
import { SimulatedProcessingMonitor } from "./SimulatedProcessingMonitor";

describe("ProcessingService", () => {
	it("returns processing data by id", async () => {
		const service = new ProcessingService(
			new MockProcessingRepository(),
			new SimulatedProcessingMonitor(new MockProcessingRepository()),
		);
		const data = await service.getProcessing("1");

		expect(data?.title).toBe("The Roman Empire");
		expect(data?.steps).toHaveLength(5);
	});

	it("returns null for unknown processing id", async () => {
		const service = new ProcessingService(
			new MockProcessingRepository(),
			new SimulatedProcessingMonitor(new MockProcessingRepository()),
		);
		await expect(service.getProcessing("unknown")).resolves.toBeNull();
	});

	it("forwards live updates from the monitor", async () => {
		vi.useFakeTimers();
		const repository = new MockProcessingRepository();
		const service = new ProcessingService(
			repository,
			new SimulatedProcessingMonitor(repository, 100),
		);
		const updates: number[] = [];

		service.subscribeToProcessing("1", (data) => {
			updates.push(data.progress);
		});

		await vi.runAllTimersAsync();

		expect(updates[0]).toBe(0);
		expect(updates.at(-1)).toBe(100);

		vi.useRealTimers();
	});
});
