import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { PipelineSourceProvider } from "@/features/pipeline/PipelineSourceContext";
import { TranslationPanel } from "@/features/translation/TranslationPanel/TranslationPanel";
import { pipelineJobService } from "@/services/pipeline/PipelineJobService";
import { transcriptService } from "@/services/transcript/TranscriptService";
import { translationService } from "@/services/translation/TranslationService";
import { renderWithProviders } from "@/test/render";

const VIDEO_ID = "550e8400-e29b-41d4-a716-446655440099";

describe("TranslationPanel", () => {
	it("renders translations and provider badge", async () => {
		vi.spyOn(transcriptService, "getTranscript").mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Hello everyone",
			duration: 3,
			segmentCount: 1,
			segments: [
				{ index: 0, startTime: 0, endTime: 3, text: "Hello everyone" },
			],
		});
		vi.spyOn(translationService, "listTranslations").mockResolvedValue([
			{
				videoId: "550e8400-e29b-41d4-a716-446655440099",
				translationId: "550e8400-e29b-41d4-a716-446655440020",
				sourceLanguage: "english",
				targetLanguage: "french",
				provider: "qwen",
				text: "Bonjour tout le monde",
				segmentCount: 1,
			},
		]);
		vi.spyOn(translationService, "getTranslation").mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			translationId: "550e8400-e29b-41d4-a716-446655440020",
			sourceLanguage: "english",
			targetLanguage: "french",
			provider: "qwen",
			text: "Bonjour tout le monde",
			segmentCount: 1,
			segments: [
				{
					index: 0,
					sourceText: "Hello everyone",
					translatedText: "Bonjour tout le monde",
				},
			],
		});

		renderWithProviders(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/translations",
				]}
			>
				<Routes>
					<Route
						path="/video/:videoId/translations"
						element={<TranslationPanel />}
					/>
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("heading", { name: "Translation" }),
			).toBeInTheDocument();
		});

		expect(screen.getByText("Bonjour tout le monde")).toBeInTheDocument();
		expect(screen.getByText("QWEN")).toBeInTheDocument();
	});

	it("calls generateTranslations when button is clicked", async () => {
		vi.spyOn(transcriptService, "getTranscript").mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Hello",
			duration: 2,
			segmentCount: 1,
			segments: [{ index: 0, startTime: 0, endTime: 2, text: "Hello" }],
		});
		vi.spyOn(translationService, "listTranslations").mockResolvedValue([]);
		const generateTranslations = vi
			.spyOn(translationService, "generateTranslations")
			.mockResolvedValue(undefined);

		const user = userEvent.setup();

		renderWithProviders(
			<MemoryRouter
				initialEntries={[
					"/video/550e8400-e29b-41d4-a716-446655440099/translations",
				]}
			>
				<Routes>
					<Route
						path="/video/:videoId/translations"
						element={<TranslationPanel />}
					/>
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Generate Translation")).toBeInTheDocument();
		});

		await user.click(
			screen.getByRole("button", { name: "Generate Translation" }),
		);

		expect(generateTranslations).toHaveBeenCalled();
	});

	it("hides generate button while translation pipeline is running", async () => {
		vi.spyOn(transcriptService, "getTranscript").mockResolvedValue({
			videoId: VIDEO_ID,
			transcriptId: "550e8400-e29b-41d4-a716-446655440010",
			language: "english",
			text: "Hello",
			duration: 2,
			segmentCount: 1,
			segments: [{ index: 0, startTime: 0, endTime: 2, text: "Hello" }],
		});
		vi.spyOn(translationService, "listTranslations").mockResolvedValue([]);
		vi.spyOn(pipelineJobService, "loadStatus").mockResolvedValue({
			sourceId: VIDEO_ID,
			activeJobs: [
				{
					jobId: "job-1",
					sourceId: VIDEO_ID,
					stage: "translation",
					status: "running",
					progressPercent: 40,
				},
			],
			completedJobs: [],
			jobsWaitingUserChoice: [],
			jobsWaitingConfirmation: [],
			failedJobs: [],
			cancelledJobs: [],
			staleArtifacts: [],
			blockedStages: [],
			requiresUserAction: false,
			message: "Translation in progress",
		});

		renderWithProviders(
			<MemoryRouter initialEntries={[`/video/${VIDEO_ID}/translations`]}>
				<PipelineSourceProvider sourceId={VIDEO_ID}>
					<Routes>
						<Route
							path="/video/:videoId/translations"
							element={<TranslationPanel />}
						/>
					</Routes>
				</PipelineSourceProvider>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.queryByRole("button", { name: "Generate Translation" }),
			).not.toBeInTheDocument();
			expect(
				screen.getByText(/Pipeline execution is in progress/i),
			).toBeInTheDocument();
		});
	});
});
