import { describe, expect, it } from "vitest";
import { mapProcessingFromApi } from "./types";

describe("mapProcessingFromApi", () => {
	it("maps pending API dto to domain processing data", () => {
		const data = mapProcessingFromApi({
			id: "job-1",
			contentId: "content-1",
			type: "summary",
			status: "pending",
			progress: 0,
			startedAt: null,
			completedAt: null,
			failedAt: null,
		});

		expect(data).toMatchObject({
			id: "job-1",
			title: "Summary processing",
			status: "pending",
			progress: 0,
			currentStep: "Pending",
		});
		expect(data.steps).toHaveLength(5);
	});

	it("maps running job with derived timeline steps", () => {
		const data = mapProcessingFromApi({
			id: "job-1",
			contentId: "content-1",
			type: "quiz",
			status: "running",
			progress: 42,
			startedAt: "2026-06-26T12:00:00+00:00",
			completedAt: null,
			failedAt: null,
		});

		expect(data.status).toBe("running");
		expect(data.currentStep).toBe("Processing");
		expect(data.steps.some((step) => step.active)).toBe(true);
	});
});
