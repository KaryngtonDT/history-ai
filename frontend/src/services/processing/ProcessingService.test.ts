import { describe, expect, it, vi } from "vitest";
import { MockProcessingRepository } from "./MockProcessingRepository";
import { ProcessingService } from "./ProcessingService";

describe("ProcessingService", () => {
	it("returns processing data by id", async () => {
		const service = new ProcessingService(new MockProcessingRepository());
		const data = await service.getProcessing("1");

		expect(data?.title).toBe("The Roman Empire");
		expect(data?.steps).toHaveLength(5);
	});

	it("returns null for unknown processing id", async () => {
		const service = new ProcessingService(new MockProcessingRepository());
		await expect(service.getProcessing("unknown")).resolves.toBeNull();
	});

	it("simulates progress from pending to completed", async () => {
		vi.useFakeTimers();
		const service = new ProcessingService(new MockProcessingRepository());
		const updates: number[] = [];

		const simulation = service.simulateProcessing("1", {
			onUpdate: (data) => updates.push(data.progress),
			stepMs: 100,
		});

		await vi.runAllTimersAsync();
		await simulation;

		expect(updates[0]).toBe(0);
		expect(updates.at(-1)).toBe(100);

		vi.useRealTimers();
	});
});
