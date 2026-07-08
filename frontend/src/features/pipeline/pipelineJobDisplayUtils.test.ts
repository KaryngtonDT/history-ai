import { describe, expect, it } from "vitest";
import {
	buildPipelineStageTimingLines,
	formatPipelineDateTime,
} from "@/features/pipeline/pipelineJobDisplayUtils";
import type { PipelineJob } from "@/services/pipeline/jobTypes";

const LABELS = {
	startedAt: "Started at {{time}}",
	notStarted: "Not started yet",
	estimatedDuration: "Estimated duration: ~{{minutes}} min",
	estimatedCompletion: "Estimated completion: {{time}}",
	actualCompletion: "Completed at {{time}}",
	actualDuration: "Actual duration: ~{{minutes}} min",
	estimationAccuracy: "Estimation accuracy: {{percent}}",
	elapsedTime: "Elapsed: ~{{minutes}} min",
	remainingMinutes: "~{{minutes}} min remaining (estimated)",
	engine: "Engine: {{engine}}",
	hardwareProfile: "Hardware: {{profile}}",
	currentStep: "Step: {{step}}",
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
		const formatted = formatPipelineDateTime(
			"2026-06-26T14:30:00.000Z",
			"en-US",
		);

		expect(formatted).toBeTruthy();
		expect(formatted).toMatch(/6\/26\/26|26\/06\/26|2026/);
	});

	it("shows start time, estimate, and remaining for running jobs", () => {
		const lines = buildPipelineStageTimingLines(
			job({
				startedAt: "2026-06-26T14:30:00.000Z",
				estimatedDurationSeconds: 600,
				estimatedRemainingSeconds: 240,
				engineId: "whisper-large",
				hardwareProfile: "gpu-8gb",
			}),
			LABELS,
			"en-US",
		);

		expect(lines.some((line) => line.startsWith("Started at"))).toBe(true);
		expect(lines).toContain("Estimated duration: ~10 min");
		expect(lines).toContain("~4 min remaining (estimated)");
		expect(lines).toContain("Engine: whisper-large");
		expect(lines).toContain("Hardware: gpu-8gb");
	});

	it("shows completion details for finished jobs", () => {
		const lines = buildPipelineStageTimingLines(
			job({
				status: "completed",
				startedAt: "2026-06-26T14:30:00.000Z",
				completedAt: "2026-06-26T14:40:00.000Z",
				actualDurationSeconds: 600,
				estimationAccuracyPercent: 92,
			}),
			LABELS,
			"en-US",
		);

		expect(lines.some((line) => line.startsWith("Completed at"))).toBe(true);
		expect(lines).toContain("Actual duration: ~10 min");
		expect(lines).toContain("Estimation accuracy: 92%");
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
