import { describe, expect, it, vi } from "vitest";
import { ValidationError } from "@/shared/errors";
import { ContentService } from "./ContentService";
import { computeStatistics } from "./computeStatistics";
import {
	EmptyMockContentRepository,
	MockContentRepository,
} from "./MockContentRepository";

describe("ContentService", () => {
	it("returns dashboard view from the repository", async () => {
		const service = new ContentService(new MockContentRepository());
		const dashboard = await service.getDashboardData();

		expect(dashboard.recentContents).toHaveLength(3);
		expect(dashboard.statistics.contents).toBe(3);
		expect(dashboard.statistics.processing).toBe(1);
	});

	it("returns library contents via listContents", async () => {
		const service = new ContentService(new MockContentRepository());

		const contents = await service.listContents();
		expect(contents[0]?.title).toBe("The Roman Empire");
	});

	it("supports an empty library", async () => {
		const service = new ContentService(new EmptyMockContentRepository());
		expect(await service.listContents()).toHaveLength(0);
	});

	it("validates PDF files", () => {
		const service = new ContentService(new MockContentRepository());
		const pdf = new File(["x"], "doc.pdf", { type: "application/pdf" });
		const txt = new File(["x"], "doc.txt", { type: "text/plain" });

		expect(service.validatePdf(pdf)).toEqual({ valid: true });
		expect(service.validatePdf(txt)).toEqual({
			valid: false,
			error: "Only PDF files are supported.",
		});
	});

	it("simulates upload progress", async () => {
		vi.useFakeTimers();
		const service = new ContentService(new MockContentRepository());
		const values: number[] = [];

		const upload = service.simulateUpload({
			onProgress: (value) => values.push(value),
			stepMs: 50,
		});

		await vi.runAllTimersAsync();
		await upload;

		expect(values.at(-1)).toBe(100);
		vi.useRealTimers();
	});

	it("creates content from a valid PDF file", async () => {
		const service = new ContentService(new MockContentRepository());
		const pdf = new File(["x"], "Roman Empire.pdf", {
			type: "application/pdf",
		});

		const result = await service.importPdf(pdf);

		expect(result.id).toBe("4");
		const contents = await service.listContents();
		expect(contents.at(-1)?.title).toBe("Roman Empire");
	});

	it("throws ValidationError for invalid PDF import", async () => {
		const service = new ContentService(new MockContentRepository());
		const txt = new File(["x"], "doc.txt", { type: "text/plain" });

		await expect(service.importPdf(txt)).rejects.toBeInstanceOf(
			ValidationError,
		);
	});
});

describe("computeStatistics", () => {
	it("derives counts from content list", () => {
		const stats = computeStatistics([
			{
				id: "1",
				title: "A",
				sourceType: "pdf",
				status: "processing",
				progress: 50,
			},
			{
				id: "2",
				title: "B",
				sourceType: "pdf",
				status: "completed",
				progress: 100,
			},
		]);

		expect(stats.contents).toBe(2);
		expect(stats.completed).toBe(1);
		expect(stats.processing).toBe(1);
	});
});
