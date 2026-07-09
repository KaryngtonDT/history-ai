import { describe, expect, it } from "vitest";
import {
	findPipelineJobForStage,
	isPipelineStageExecutionLocked,
	mapPipelineJobToArtifactStatus,
} from "@/features/pipeline/pipelineJobStateUtils";
import type { PipelineSourceStatus } from "@/services/pipeline/jobTypes";

function status(
	partial: Partial<PipelineSourceStatus> = {},
): PipelineSourceStatus {
	return {
		sourceId: "source-1",
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

describe("pipelineJobStateUtils", () => {
	it("locks generate actions while translation is running", () => {
		const pipeline = status({
			activeJobs: [
				{
					jobId: "job-1",
					sourceId: "source-1",
					stage: "translation",
					status: "running",
					progressPercent: 40,
				},
			],
		});

		expect(isPipelineStageExecutionLocked(pipeline, "translation")).toBe(true);
		expect(findPipelineJobForStage(pipeline, "translation")?.status).toBe(
			"running",
		);
	});

	it("maps running pipeline job to in_progress journey status", () => {
		expect(
			mapPipelineJobToArtifactStatus(
				{
					jobId: "job-1",
					sourceId: "source-1",
					stage: "translation",
					status: "running",
					progressPercent: 40,
				},
				true,
			),
		).toBe("in_progress");
	});
});
