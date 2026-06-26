import { describe, expect, it, vi } from "vitest";
import { ImportService } from "./ImportService";

describe("ImportService", () => {
	it("accepts PDF files", () => {
		const service = new ImportService();
		const file = new File(["content"], "history.pdf", {
			type: "application/pdf",
		});

		expect(service.validatePdf(file)).toEqual({ valid: true });
	});

	it("rejects non-PDF files", () => {
		const service = new ImportService();
		const file = new File(["content"], "notes.txt", { type: "text/plain" });

		expect(service.validatePdf(file)).toEqual({
			valid: false,
			error: "Only PDF files are supported.",
		});
	});

	it("simulates upload progress from 0 to 100", async () => {
		vi.useFakeTimers();
		const service = new ImportService();
		const progressValues: number[] = [];

		const uploadPromise = service.simulateUpload({
			onProgress: (value) => progressValues.push(value),
			stepMs: 50,
		});

		await vi.runAllTimersAsync();
		await uploadPromise;

		expect(progressValues).toEqual([0, 20, 40, 60, 80, 100]);

		vi.useRealTimers();
	});
});
