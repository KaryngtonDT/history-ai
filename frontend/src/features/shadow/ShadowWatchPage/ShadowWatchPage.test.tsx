import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import { ShadowWatchPage } from "@/features/shadow/ShadowWatchPage";
import { videoRenderService } from "@/services/render/VideoRenderService";
import { shadowService } from "@/services/shadow/ShadowService";
import { DEFAULT_SHADOW_VOICE_PREFERENCE } from "@/services/shadow/types";
import { transcriptService } from "@/services/transcript/TranscriptService";
import { videoService } from "@/services/video/VideoService";
import { renderWithProviders } from "@/test/render";

const VIDEO_ID = "550e8400-e29b-41d4-a716-446655440099";

describe("ShadowWatchPage", () => {
	it("renders watch mode and starts a session", async () => {
		vi.spyOn(videoRenderService, "listRenders").mockResolvedValue([]);
		vi.spyOn(videoService, "getStatus").mockResolvedValue({
			videoId: VIDEO_ID,
			status: "completed",
			originalFilename: "lecture.mp4",
			language: "unknown",
			createdAt: new Date().toISOString(),
		});
		vi.spyOn(transcriptService, "getTranscript").mockResolvedValue({
			videoId: VIDEO_ID,
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Hello world.",
			duration: 5,
			segmentCount: 1,
			segments: [{ index: 0, startTime: 0, endTime: 5, text: "Hello world." }],
		});
		vi.spyOn(shadowService, "startSession").mockResolvedValue({
			sessionId: "550e8400-e29b-41d4-a716-446655440020",
			videoId: VIDEO_ID,
			playbackState: "playing",
			targetLanguage: "fr",
			currentTimeSeconds: 0,
			currentTranscriptSegmentIndex: null,
			currentTranslationSegmentIndex: null,
			contentId: VIDEO_ID,
			conversationId: null,
			interactions: [],
			policy: {
				enabled: false,
				maxInterventionsPerMinute: 2,
				minSecondsBetweenInterventions: 45,
				challengeLevel: "easy",
				explanationStyle: "short",
				autoResume: false,
				allowAutoPause: true,
			},
			voicePreference: DEFAULT_SHADOW_VOICE_PREFERENCE,
		});
		vi.spyOn(shadowService, "getContext").mockResolvedValue({
			videoId: VIDEO_ID,
			currentTimeSeconds: 0,
			targetLanguage: "fr",
			conversationId: null,
			currentTranscriptSegment: {
				index: 0,
				startTime: 0,
				endTime: 5,
				text: "Hello world.",
			},
			currentTranslationSegment: null,
			previousTranscriptSegment: null,
			nextTranscriptSegment: null,
			previousTranslationSegment: null,
			nextTranslationSegment: null,
			nearbyTranscriptContext: "Hello world.",
			nearbyTranslationContext: "",
			currentSpeaker: null,
			recentInteractions: [],
			conversationMemory: [],
		});

		renderWithProviders(
			<MemoryRouter initialEntries={[`/video/${VIDEO_ID}/watch`]}>
				<Routes>
					<Route path="/video/:videoId/watch" element={<ShadowWatchPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("heading", { name: "Shadow" }),
			).toBeInTheDocument();
		});

		expect(
			screen.getByText("Voice input unavailable — use text below."),
		).toBeInTheDocument();
	});

	it("submits a question to Shadow", async () => {
		vi.spyOn(videoRenderService, "listRenders").mockResolvedValue([]);
		vi.spyOn(videoService, "getStatus").mockResolvedValue({
			videoId: VIDEO_ID,
			status: "completed",
			originalFilename: "lecture.mp4",
			language: "unknown",
			createdAt: new Date().toISOString(),
		});
		vi.spyOn(transcriptService, "getTranscript").mockResolvedValue({
			videoId: VIDEO_ID,
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Hello world.",
			duration: 5,
			segmentCount: 1,
			segments: [{ index: 0, startTime: 0, endTime: 5, text: "Hello world." }],
		});
		vi.spyOn(shadowService, "startSession").mockResolvedValue({
			sessionId: "550e8400-e29b-41d4-a716-446655440020",
			videoId: VIDEO_ID,
			playbackState: "playing",
			targetLanguage: "fr",
			currentTimeSeconds: 0,
			currentTranscriptSegmentIndex: null,
			currentTranslationSegmentIndex: null,
			contentId: null,
			conversationId: null,
			interactions: [],
			policy: {
				enabled: false,
				maxInterventionsPerMinute: 2,
				minSecondsBetweenInterventions: 45,
				challengeLevel: "easy",
				explanationStyle: "short",
				autoResume: false,
				allowAutoPause: true,
			},
			voicePreference: DEFAULT_SHADOW_VOICE_PREFERENCE,
		});
		vi.spyOn(shadowService, "getContext").mockResolvedValue(null);
		const askSpy = vi.spyOn(shadowService, "askQuestion").mockResolvedValue({
			sessionId: "550e8400-e29b-41d4-a716-446655440020",
			answer: "Shadow explains the sentence at 0.0s.",
			currentTimeSeconds: 0,
			currentTranscriptSegmentIndex: 0,
			currentTranslationSegmentIndex: null,
			answerLanguage: "fr",
			speechLanguage: "fr",
			fallbackUsed: false,
			reason: "target_language",
			session: {
				sessionId: "550e8400-e29b-41d4-a716-446655440020",
				videoId: VIDEO_ID,
				playbackState: "playing",
				targetLanguage: "fr",
				currentTimeSeconds: 0,
				currentTranscriptSegmentIndex: 0,
				currentTranslationSegmentIndex: null,
				contentId: null,
				conversationId: null,
				interactions: [
					{
						kind: "question",
						participant: "user",
						videoTimestamp: 0,
						text: "Explain this sentence.",
					},
					{
						kind: "answer",
						participant: "shadow",
						videoTimestamp: 0,
						text: "Shadow explains the sentence at 0.0s.",
					},
				],
				policy: {
					enabled: false,
					maxInterventionsPerMinute: 2,
					minSecondsBetweenInterventions: 45,
					challengeLevel: "easy",
					explanationStyle: "short",
					autoResume: false,
					allowAutoPause: true,
				},
				voicePreference: DEFAULT_SHADOW_VOICE_PREFERENCE,
			},
		});

		const user = userEvent.setup();

		renderWithProviders(
			<MemoryRouter initialEntries={[`/video/${VIDEO_ID}/watch`]}>
				<Routes>
					<Route path="/video/:videoId/watch" element={<ShadowWatchPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByLabelText("Ask Shadow")).toBeInTheDocument();
		});

		await user.type(
			screen.getByLabelText("Ask Shadow"),
			"Explain this sentence.",
		);
		await user.click(screen.getByRole("button", { name: "Ask Shadow" }));

		await waitFor(() => {
			expect(askSpy).toHaveBeenCalled();
		});
	});

	it("shows transcript choice panel and stops transcript polling while waiting", async () => {
		const getTranscriptSpy = vi
			.spyOn(transcriptService, "loadTranscript")
			.mockResolvedValue({ transcript: null, unavailableDetail: null });
		vi.spyOn(videoRenderService, "listRenders").mockResolvedValue([]);
		vi.spyOn(videoService, "getStatus").mockResolvedValue({
			videoId: VIDEO_ID,
			status: "queued",
			originalFilename: "lecture.mp4",
			language: "unknown",
			createdAt: new Date().toISOString(),
		});
		vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue({
			sourceId: VIDEO_ID,
			activeJobs: [],
			completedJobs: [],
			jobsWaitingUserChoice: [
				{
					jobId: "job-1",
					sourceId: VIDEO_ID,
					stage: "speech_to_text",
					status: "waiting_user_choice",
					progressPercent: 0,
				},
			],
			jobsWaitingConfirmation: [],
			failedJobs: [],
			cancelledJobs: [],
			staleArtifacts: [],
			blockedStages: ["speech_to_text"],
			requiresUserAction: true,
			message: "Choose transcript source",
		});

		renderWithProviders(
			<MemoryRouter initialEntries={[`/video/${VIDEO_ID}/watch`]}>
				<Routes>
					<Route path="/video/:videoId/watch" element={<ShadowWatchPage />} />
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

		expect(getTranscriptSpy).toHaveBeenCalledTimes(1);
	});
});
