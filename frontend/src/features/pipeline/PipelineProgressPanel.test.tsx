import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi, beforeEach } from "vitest";
import { PipelineProgressPanel } from "@/features/pipeline/PipelineProgressPanel";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import type { PipelineSourceStatus } from "@/services/pipeline/jobTypes";
import { renderWithProviders } from "@/test/render";

const SOURCE_ID = "550e8400-e29b-41d4-a716-446655440099";

function pipelineStatus(
	partial: Partial<PipelineSourceStatus> = {},
): PipelineSourceStatus {
	return {
		sourceId: SOURCE_ID,
		activeJobs: [],
		completedJobs: [],
		jobsWaitingUserChoice: [],
		jobsWaitingConfirmation: [],
		failedJobs: [],
		cancelledJobs: [],
		staleArtifacts: [],
		blockedStages: [],
		requiresUserAction: true,
		message: "Choose transcript source",
		...partial,
	};
}

describe("PipelineProgressPanel", () => {
	beforeEach(() => {
		vi.restoreAllMocks();
	});

	it("shows transcript choice dialog when captions are waiting for user choice", async () => {
		vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue(
			pipelineStatus({
				jobsWaitingUserChoice: [
					{
						jobId: "job-1",
						sourceId: SOURCE_ID,
						stage: "speech_to_text",
						status: "waiting_user_choice",
						progressPercent: 0,
					},
				],
			}),
		);

		renderWithProviders(<PipelineProgressPanel sourceId={SOURCE_ID} />);

		await waitFor(() => {
			expect(
				screen.getByRole("dialog", {
					name: /original youtube transcript found/i,
				}),
			).toBeInTheDocument();
		});
	});

	it("submits youtube transcript choice", async () => {
		const loadSpy = vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue(
			pipelineStatus({
				jobsWaitingUserChoice: [
					{
						jobId: "job-1",
						sourceId: SOURCE_ID,
						stage: "speech_to_text",
						status: "waiting_user_choice",
						progressPercent: 0,
					},
				],
			}),
		);
		const submitSpy = vi
			.spyOn(pipelineJobService, "submitChoice")
			.mockResolvedValue({
				jobId: "job-1",
				sourceId: SOURCE_ID,
				stage: "speech_to_text",
				status: "waiting_user_confirmation",
				progressPercent: 100,
			});

		const user = userEvent.setup();
		renderWithProviders(<PipelineProgressPanel sourceId={SOURCE_ID} />);

		await waitFor(() => {
			expect(screen.getByText("Use YouTube transcript")).toBeInTheDocument();
		});

		await user.click(screen.getByText("Use YouTube transcript"));

		await waitFor(() => {
			expect(submitSpy).toHaveBeenCalledWith(
				SOURCE_ID,
				"speech_to_text",
				"youtube_transcript",
			);
			expect(loadSpy.mock.calls.length).toBeGreaterThan(1);
		});
	});
});
