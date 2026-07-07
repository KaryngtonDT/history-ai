import { describe, expect, it } from "vitest";
import type { PipelineJob, PipelineSourceStatus } from "@/services/pipeline/jobTypes";
import {
	computeNonNegativeElapsedSeconds,
	formatJobElapsedSeconds,
	isJobWaitingForTranscriptChoice,
	isPipelineWaitingForTranscriptChoice,
	resolveJobsWaitingUserChoice,
} from "./pipelineChoiceUtils";

function job(partial: Partial<PipelineJob> & Pick<PipelineJob, "jobId">): PipelineJob {
	return {
		sourceId: "video-1",
		stage: "speech_to_text",
		status: "queued",
		progressPercent: 0,
		...partial,
	};
}

function status(partial: Partial<PipelineSourceStatus>): PipelineSourceStatus {
	return {
		sourceId: "video-1",
		activeJobs: [],
		completedJobs: [],
		jobsWaitingUserChoice: [],
		jobsWaitingConfirmation: [],
		failedJobs: [],
		cancelledJobs: [],
		staleArtifacts: [],
		blockedStages: [],
		requiresUserAction: false,
		message: "",
		...partial,
	};
}

describe("pipelineChoiceUtils", () => {
	it("treats queued jobs with userChoiceRequired as waiting for choice", () => {
		expect(
			isJobWaitingForTranscriptChoice(
				job({
					jobId: "j1",
					status: "queued",
					userChoiceRequired: true,
				}),
			),
		).toBe(true);
	});

	it("merges waiting jobs from active and dedicated buckets", () => {
		const waitingJob = job({
			jobId: "j1",
			status: "waiting_user_choice",
		});
		const queuedChoiceJob = job({
			jobId: "j2",
			status: "queued",
			userChoiceRequired: true,
		});

		const resolved = resolveJobsWaitingUserChoice(
			status({
				jobsWaitingUserChoice: [waitingJob],
				activeJobs: [queuedChoiceJob],
			}),
		);

		expect(resolved).toHaveLength(2);
		expect(isPipelineWaitingForTranscriptChoice(status({ activeJobs: [queuedChoiceJob] }))).toBe(
			true,
		);
	});

	it("never returns negative elapsed seconds", () => {
		const future = Date.now() + 60_000;
		expect(computeNonNegativeElapsedSeconds(future)).toBe(0);
		expect(computeNonNegativeElapsedSeconds(null)).toBeNull();
		expect(
			formatJobElapsedSeconds(
				job({
					jobId: "j1",
					elapsedSeconds: -86100,
				}),
			),
		).toBe(0);
	});
});
