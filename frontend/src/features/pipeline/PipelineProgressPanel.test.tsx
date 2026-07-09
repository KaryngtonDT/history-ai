import { act, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { PipelineProgressPanel } from "@/features/pipeline/PipelineProgressPanel";
import {
	LIVE_PIPELINE_POLL_MS,
	LIVE_PIPELINE_TICK_MS,
} from "@/features/pipeline/pipelineLiveProgressUtils";
import type { PipelineSourceStatus } from "@/services/pipeline/jobTypes";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
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
		const loadSpy = vi
			.spyOn(pipelineJobService, "loadStatus")
			.mockResolvedValue(
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

	it("shows start time and duration estimate for running jobs", async () => {
		vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue(
			pipelineStatus({
				activeJobs: [
					{
						jobId: "job-translation",
						sourceId: SOURCE_ID,
						stage: "translation",
						status: "running",
						progressPercent: 25,
						startedAt: "2026-06-26T14:30:00.000Z",
						estimatedDurationSeconds: 600,
						elapsedSeconds: 180,
						estimatedRemainingSeconds: 240,
					},
				],
				requiresUserAction: false,
				message: "Translation in progress",
			}),
		);

		renderWithProviders(<PipelineProgressPanel sourceId={SOURCE_ID} />);

		await waitFor(() => {
			expect(screen.getByText(/Started at/i)).toBeInTheDocument();
			expect(
				screen.getByText(/Estimated duration: ~10 min/i),
			).toBeInTheDocument();
			expect(screen.getByText(/~04:00 remaining/i)).toBeInTheDocument();
		});
	});

	it("polls running jobs every second without page refresh", async () => {
		vi.useFakeTimers();

		try {
			const loadSpy = vi
				.spyOn(pipelineJobService, "loadStatus")
				.mockResolvedValue(
					pipelineStatus({
						activeJobs: [
							{
								jobId: "job-stt",
								sourceId: SOURCE_ID,
								stage: "speech_to_text",
								status: "running",
								progressPercent: 20,
								isLive: true,
								startedAt: "2026-07-09T00:00:00.000Z",
								serverNow: "2026-07-09T00:01:00.000Z",
								elapsedSeconds: 60,
								estimatedDurationSeconds: 600,
								estimatedRemainingSeconds: 540,
								hardwareProfileCode: "CPU_ONLY",
							},
						],
						requiresUserAction: false,
						message: "Transcription in progress",
					}),
				);

			renderWithProviders(<PipelineProgressPanel sourceId={SOURCE_ID} />);

			await act(async () => {
				await Promise.resolve();
			});

			expect(loadSpy).toHaveBeenCalledTimes(1);

			await act(async () => {
				await vi.advanceTimersByTimeAsync(LIVE_PIPELINE_POLL_MS);
			});

			expect(loadSpy.mock.calls.length).toBeGreaterThanOrEqual(2);
		} finally {
			vi.useRealTimers();
		}
	});

	it("updates elapsed display on client tick without refresh", async () => {
		vi.useFakeTimers();
		vi.setSystemTime(new Date("2026-07-09T00:02:00.000Z"));

		try {
			vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue(
				pipelineStatus({
					activeJobs: [
						{
							jobId: "job-stt",
							sourceId: SOURCE_ID,
							stage: "speech_to_text",
							status: "running",
							progressPercent: 32,
							isLive: true,
							startedAt: "2026-07-09T00:00:00.000Z",
							serverNow: "2026-07-09T00:02:00.000Z",
							elapsedSeconds: 120,
							estimatedDurationSeconds: 600,
							estimatedRemainingSeconds: 480,
							processingSpeedRatio: 2.8,
							hardwareProfileCode: "NVIDIA",
							checkpointLabel: "Transcribing",
						},
					],
					requiresUserAction: false,
					message: "Transcription in progress",
				}),
			);

			renderWithProviders(
				<PipelineProgressPanel sourceId={SOURCE_ID} pollMs={60_000} />,
			);

			await act(async () => {
				await Promise.resolve();
			});

			expect(screen.getByText(/Elapsed: 02:00/i)).toBeInTheDocument();
			expect(screen.getByText(/Hardware profile: NVIDIA/i)).toBeInTheDocument();
			expect(screen.getByText(/Speed: 2.8× real-time/i)).toBeInTheDocument();

			await act(async () => {
				await vi.advanceTimersByTimeAsync(LIVE_PIPELINE_TICK_MS);
			});

			expect(screen.getByText(/Elapsed: 02:01/i)).toBeInTheDocument();
		} finally {
			vi.useRealTimers();
		}
	});
});
