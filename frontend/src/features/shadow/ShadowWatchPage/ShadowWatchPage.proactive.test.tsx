import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { ShadowWatchPage } from "@/features/shadow/ShadowWatchPage";
import { videoRenderService } from "@/services/render/VideoRenderService";
import { shadowService } from "@/services/shadow/ShadowService";
import { DEFAULT_SHADOW_VOICE_PREFERENCE } from "@/services/shadow/types";
import { transcriptService } from "@/services/transcript/TranscriptService";
import { videoService } from "@/services/video/VideoService";
import { renderWithProviders } from "@/test/render";

const VIDEO_ID = "550e8400-e29b-41d4-a716-446655440099";

const defaultTranscript = {
	videoId: VIDEO_ID,
	transcriptId: "550e8400-e29b-41d4-a716-446655440010",
	language: "english" as const,
	text: "Hello world.",
	duration: 5,
	segmentCount: 1,
	segments: [{ index: 0, startTime: 0, endTime: 5, text: "Hello world." }],
};

function mockShadowWatchBootstrap(): void {
	vi.spyOn(videoRenderService, "listRenders").mockResolvedValue([]);
	vi.spyOn(transcriptService, "getTranscript").mockResolvedValue(
		defaultTranscript,
	);
	vi.spyOn(videoService, "getStatus").mockResolvedValue({
		videoId: VIDEO_ID,
		status: "completed",
		originalFilename: "lecture.mp4",
		language: "unknown",
		createdAt: new Date().toISOString(),
	});
	vi.spyOn(videoService, "processVideo").mockResolvedValue({
		status: "queued",
	});
}

const defaultPolicy = {
	enabled: false,
	maxInterventionsPerMinute: 2,
	minSecondsBetweenInterventions: 45,
	challengeLevel: "easy" as const,
	explanationStyle: "short" as const,
	autoResume: false,
	allowAutoPause: true,
};

const defaultVoicePreference = DEFAULT_SHADOW_VOICE_PREFERENCE;

describe("ShadowWatchPage proactive tutor", () => {
	it("disables intervention checks when proactive mode is off", async () => {
		mockShadowWatchBootstrap();
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
			policy: defaultPolicy,
			voicePreference: defaultVoicePreference,
		});
		vi.spyOn(shadowService, "getContext").mockResolvedValue(null);
		const checkSpy = vi
			.spyOn(shadowService, "checkIntervention")
			.mockResolvedValue({
				hasIntervention: false,
				intervention: null,
				recommendPause: false,
				recommendResume: false,
				session: {
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
					policy: defaultPolicy,
					voicePreference: defaultVoicePreference,
				},
			});

		renderWithProviders(
			<MemoryRouter initialEntries={[`/video/${VIDEO_ID}/watch`]}>
				<Routes>
					<Route path="/video/:videoId/watch" element={<ShadowWatchPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Proactive tutor off")).toBeInTheDocument();
		});

		expect(checkSpy).not.toHaveBeenCalled();
	});

	it("updates policy when proactive mode is enabled", async () => {
		mockShadowWatchBootstrap();
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
			policy: defaultPolicy,
			voicePreference: defaultVoicePreference,
		});
		vi.spyOn(shadowService, "getContext").mockResolvedValue(null);
		const updateSpy = vi
			.spyOn(shadowService, "updateInterventionPolicy")
			.mockResolvedValue({
				...defaultPolicy,
				enabled: true,
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
			expect(screen.getByLabelText("Proactive mode")).toBeInTheDocument();
		});

		await user.click(screen.getByLabelText("Proactive mode"));

		await waitFor(() => {
			expect(updateSpy).toHaveBeenCalled();
		});
	});

	it("updates voice preference when speaking language changes", async () => {
		mockShadowWatchBootstrap();
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
			policy: defaultPolicy,
			voicePreference: defaultVoicePreference,
		});
		vi.spyOn(shadowService, "getContext").mockResolvedValue(null);
		const updateSpy = vi
			.spyOn(shadowService, "updateVoicePreference")
			.mockResolvedValue({
				mode: "manual",
				manualLanguage: "de",
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
			expect(
				screen.getByLabelText("Shadow speaking language"),
			).toBeInTheDocument();
		});

		await user.selectOptions(
			screen.getByLabelText("Shadow speaking language"),
			"de",
		);

		await waitFor(() => {
			expect(updateSpy).toHaveBeenCalled();
		});
	});
});
