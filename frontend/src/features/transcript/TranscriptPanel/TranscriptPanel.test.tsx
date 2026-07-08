import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { beforeEach, describe, expect, it, vi } from "vitest";
import { TranscriptPanel } from "@/features/transcript/TranscriptPanel/TranscriptPanel";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import { transcriptService } from "@/services/transcript/TranscriptService";
import { renderWithProviders } from "@/test/render";

describe("TranscriptPanel", () => {
	beforeEach(() => {
		vi.restoreAllMocks();
	});

	it("renders transcript segments and highlights selection", async () => {
		vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue({
			sourceId: "550e8400-e29b-41d4-a716-446655440099",
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
		});
		vi.spyOn(transcriptService, "getTranscript").mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "First segment. Second segment.",
			duration: 6,
			segmentCount: 2,
			segments: [
				{
					index: 0,
					startTime: 0,
					endTime: 3,
					text: "First segment.",
				},
				{
					index: 1,
					startTime: 3,
					endTime: 6,
					text: "Second segment.",
				},
			],
		});

		const user = userEvent.setup();

		renderWithProviders(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/transcript",
				]}
			>
				<Routes>
					<Route
						path="/video/:videoId/transcript"
						element={<TranscriptPanel />}
					/>
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Transcript")).toBeInTheDocument();
		});

		expect(screen.getByText("First segment.")).toBeInTheDocument();
		expect(screen.getByText("Second segment.")).toBeInTheDocument();

		await user.click(screen.getByRole("button", { name: /Second segment\./ }));

		expect(
			screen.getByRole("button", { name: /Second segment\./ }).className,
		).toContain("segmentActive");
	});

	it("shows choice panel instead of polling transcript when waiting for user choice", async () => {
		vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue({
			sourceId: "550e8400-e29b-41d4-a716-446655440099",
			activeJobs: [],
			completedJobs: [],
			jobsWaitingUserChoice: [
				{
					jobId: "job-1",
					sourceId: "550e8400-e29b-41d4-a716-446655440099",
					stage: "speech_to_text",
					status: "waiting_user_choice",
					progressPercent: 0,
				},
			],
			jobsWaitingConfirmation: [],
			failedJobs: [],
			cancelledJobs: [],
			staleArtifacts: [],
			blockedStages: [],
			requiresUserAction: true,
			message: "Choose",
		});
		const getTranscriptSpy = vi
			.spyOn(transcriptService, "getTranscript")
			.mockResolvedValue(null);

		renderWithProviders(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/transcript",
				]}
			>
				<Routes>
					<Route
						path="/video/:videoId/transcript"
						element={<TranscriptPanel />}
					/>
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("dialog", {
					name: /original youtube transcript found/i,
				}),
			).toBeInTheDocument();
		});

		expect(getTranscriptSpy).not.toHaveBeenCalled();
	});

	it("shows empty state when transcript is unavailable", async () => {
		vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue({
			sourceId: "550e8400-e29b-41d4-a716-446655440099",
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
		});
		vi.spyOn(transcriptService, "getTranscript").mockResolvedValue(null);

		renderWithProviders(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/transcript",
				]}
			>
				<Routes>
					<Route
						path="/video/:videoId/transcript"
						element={<TranscriptPanel />}
					/>
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Transcript unavailable")).toBeInTheDocument();
		});
	});
});
