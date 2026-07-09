import { describe, expect, it } from "vitest";
import {
	applyLiveProgressTick,
	attachPipelineJobClock,
	computeLiveElapsedSeconds,
	computeLiveRemainingSeconds,
	computeServerClockOffset,
	formatDurationClock,
	formatProcessingSpeedRatio,
	hasRunningPipelineJobs,
	resolveHardwareProfileDisplay,
} from "@/features/pipeline/pipelineLiveProgressUtils";
import type { PipelineJob } from "@/services/pipeline/jobTypes";

function runningJob(partial: Partial<PipelineJob> = {}): PipelineJob {
	return {
		jobId: "job-1",
		sourceId: "source-1",
		stage: "speech_to_text",
		status: "running",
		progressPercent: 32,
		startedAt: "2026-07-09T00:00:00.000Z",
		estimatedDurationSeconds: 600,
		isLive: true,
		serverNow: "2026-07-09T00:06:12.000Z",
		elapsedSeconds: 372,
		estimatedRemainingSeconds: 788,
		...partial,
	};
}

describe("pipelineLiveProgressUtils", () => {
	it("computes server clock offset", () => {
		const offset = computeServerClockOffset(
			"2026-07-09T00:06:12.000Z",
			Date.parse("2026-07-09T00:06:00.000Z"),
		);

		expect(offset).toBe(12_000);
	});

	it("updates elapsed time from startedAt and server clock", () => {
		const job = attachPipelineJobClock(
			runningJob(),
			Date.parse("2026-07-09T00:06:12.000Z"),
		);

		const elapsed = computeLiveElapsedSeconds(
			job,
			Date.parse("2026-07-09T00:07:12.000Z"),
		);

		expect(elapsed).toBe(432);
	});

	it("recalculates remaining time from progress and elapsed", () => {
		const remaining = computeLiveRemainingSeconds(runningJob(), 372, 32);

		expect(remaining).toBeGreaterThan(700);
		expect(remaining).toBeLessThan(820);
	});

	it("moves estimated completion when tick advances", () => {
		const job = attachPipelineJobClock(
			runningJob(),
			Date.parse("2026-07-09T00:06:12.000Z"),
		);
		const first = applyLiveProgressTick(
			job,
			Date.parse("2026-07-09T00:06:42.000Z"),
		);
		const second = applyLiveProgressTick(
			job,
			Date.parse("2026-07-09T00:07:12.000Z"),
		);

		expect(first.elapsedSeconds).toBe(402);
		expect(second.elapsedSeconds).toBe(432);
		expect(first.estimatedCompletionAt).toBeTruthy();
		expect(second.estimatedCompletionAt).toBeTruthy();
		expect(second.estimatedRemainingSeconds).not.toBe(
			first.estimatedRemainingSeconds,
		);
	});

	it("freezes values for completed jobs", () => {
		const completed = applyLiveProgressTick(
			{
				...runningJob({
					status: "completed",
					isLive: false,
					liveFrozen: true,
					progressPercent: 100,
					elapsedSeconds: 540,
					estimatedRemainingSeconds: 0,
				}),
			},
			Date.parse("2026-07-09T00:20:00.000Z"),
		);

		expect(completed.elapsedSeconds).toBe(540);
		expect(completed.estimatedRemainingSeconds).toBe(0);
	});

	it("formats duration and speed labels", () => {
		expect(formatDurationClock(372)).toBe("06:12");
		expect(formatDurationClock(4267)).toBe("01:11:07");
		expect(formatProcessingSpeedRatio(2.84)).toBe("2.8× real-time");
	});

	it("prefers hardware profile code for display", () => {
		expect(
			resolveHardwareProfileDisplay({
				...runningJob(),
				hardwareProfile: "unknown",
				hardwareProfileCode: "NVIDIA",
			}),
		).toBe("NVIDIA");
	});

	it("detects running jobs for adaptive polling", () => {
		expect(hasRunningPipelineJobs([runningJob()])).toBe(true);
		expect(
			hasRunningPipelineJobs([
				runningJob({ status: "completed", isLive: false }),
			]),
		).toBe(false);
	});
});
