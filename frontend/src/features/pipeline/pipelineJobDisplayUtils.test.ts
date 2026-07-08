import { describe, expect, it } from "vitest";
import {
	buildPipelineStageTimingLines,
	formatPipelineStartedAt,
} from "@/features/pipeline/pipelineJobDisplayUtils";
import type { PipelineJob } from "@/services/pipeline/jobTypes";

const LABELS = {
	startedAt: "Started at {{time}}",
	notStarted: "Not started yet",
	estimatedDuration: "Estimated duration: ~{{minutes}} min",
	remainingMinutes: "~{{minutes}} min remaining (estimated)",
};

function job(partial: Partial<PipelineJob> = {}): PipelineJob {
	return {
		jobId: "job-1",
		sourceId: "source-1",
		stage: "translation",
		status: "running",
		progressPercent: 10,
		...partial,
	};
}

describe("pipelineJobDisplayUtils", () => {
	it("formats startedAt with locale", () => {
		const formatted = formatPipelineStartedAt("2026-06-26T14:30:00.000Z", "en-US");

		expect(formatted).toBeTruthy();
		expect(formatted).toMatch(/6\/26\/26|26\/06\/26|2026/);
	});

	it("shows start time, estimate, and remaining for running jobs", () => {
		const lines = buildPipelineStageTimingLines(
			job({
				startedAt: "2026-06-26T14:30:00.000Z",
				estimatedDurationSeconds: 600,
				estimatedRemainingSeconds: 240,
			}),
			LABELS,
			"en-US",
		);

		expect(lines.some((line) => line.startsWith("Started at"))).toBe(true);
		expect(lines).toContain("Estimated duration: ~10 min");
		expect(lines).toContain("~4 min remaining (estimated)");
	});

	it("shows not started for queued jobs without startedAt", () => {
		const lines = buildPipelineStageTimingLines(
			job({ status: "queued", startedAt: null }),
			LABELS,
			"en-US",
		);

		expect(lines).toContain("Not started yet");
	});
});
