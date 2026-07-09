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
	estimatedCompletion: "Estimated finish: {{time}}",
	actualCompletion: "Completed at {{time}}",
	actualDuration: "Actual duration: {{time}}",
	estimationAccuracy: "Prediction accuracy: {{percent}}",
	elapsedTime: "Elapsed: {{time}}",
	remainingTime: "~{{time}} remaining (estimated)",
	engine: "Engine: {{engine}}",
	engineVersion: "Engine version: {{version}}",
	provider: "Provider: {{provider}}",
	hardwareProfile: "Hardware profile: {{profile}}",
	currentStep: "Stage: {{step}}",
	checkpoint: "Checkpoint: {{checkpoint}}",
	processingSpeed: "Speed: {{speed}}",
	currentSegment: "Current segment: {{current}} / {{total}}",
	audioProcessed: "Audio processed: {{processed}} / {{total}}",
	worker: "Worker: {{worker}}",
	dockerContainer: "Docker container: {{container}}",
	waitingForWorker: "Waiting for worker update...",
	averageSpeed: "Average speed: {{speed}}",
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
				elapsedSeconds: 180,
				estimatedRemainingSeconds: 240,
				engineId: "whisper-large",
				hardwareProfileCode: "NVIDIA",
				processingSpeedRatio: 2.8,
				audioProcessedSeconds: 1335,
				audioTotalSeconds: 4268,
			}),
			LABELS,
			"en-US",
		);

		expect(lines.some((line) => line.startsWith("Started at"))).toBe(true);
		expect(lines).toContain("Estimated duration: ~10 min");
		expect(lines).toContain("Elapsed: 03:00");
		expect(lines).toContain("~04:00 remaining (estimated)");
		expect(lines).toContain("Engine: whisper-large");
		expect(lines).toContain("Hardware profile: NVIDIA");
		expect(lines).toContain("Speed: 2.8× real-time");
		expect(lines).toContain("Audio processed: 22:15 / 01:11:08");
	});

	it("shows worker waiting message when stale", () => {
		const lines = buildPipelineStageTimingLines(
			job({
				workerStatus: "waiting_for_update",
				workerStale: true,
			}),
			LABELS,
			"en-US",
		);

		expect(lines).toContain("Waiting for worker update...");
	});

	it("shows completion details for finished jobs", () => {
		const lines = buildPipelineStageTimingLines(
			job({
				status: "completed",
				startedAt: "2026-06-26T14:30:00.000Z",
				completedAt: "2026-06-26T14:40:00.000Z",
				actualDurationSeconds: 600,
				estimationAccuracyPercent: 92,
				processingSpeedRatio: 2.5,
			}),
			LABELS,
			"en-US",
		);

		expect(lines.some((line) => line.startsWith("Completed at"))).toBe(true);
		expect(lines).toContain("Actual duration: 10:00");
		expect(lines).toContain("Prediction accuracy: 92%");
		expect(lines).toContain("Average speed: 2.5× real-time");
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
