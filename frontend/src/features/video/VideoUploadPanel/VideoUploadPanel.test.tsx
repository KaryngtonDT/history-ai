import { fireEvent, render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { VideoUploadPanel } from "@/features/video/VideoUploadPanel/VideoUploadPanel";
import { MOCK_PREVIEW_INTELLIGENCE } from "@/services/intelligence/MockVideoIntelligenceRepository";
import { videoIntelligenceService } from "@/services/intelligence/VideoIntelligenceService";
import { MOCK_PREVIEW_OPTIMIZATION } from "@/services/optimization/MockOptimizationRepository";
import { optimizationService } from "@/services/optimization/OptimizationService";
import { orchestratorService } from "@/services/orchestrator/OrchestratorService";
import { videoService } from "@/services/video/VideoService";
import { ValidationError } from "@/shared/errors";

describe("VideoUploadPanel", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	beforeEach(() => {
		vi.spyOn(orchestratorService, "loadRecommendation").mockResolvedValue({
			id: "550e8400-e29b-41d4-a716-446655440099",
			strategy: "balanced",
			explanation: "Balanced pipeline.",
			estimatedDurationSeconds: 240,
			estimatedQuality: 4,
			estimatedVramGb: 8,
			reasons: ["Balanced strategy selected."],
			stages: [],
		});
		vi.spyOn(
			videoIntelligenceService,
			"loadPreviewIntelligence",
		).mockResolvedValue(MOCK_PREVIEW_INTELLIGENCE);
		vi.spyOn(optimizationService, "loadPreviewOptimization").mockResolvedValue(
			MOCK_PREVIEW_OPTIMIZATION,
		);
	});

	it("rejects unsupported files", () => {
		render(
			<MemoryRouter>
				<VideoUploadPanel />
			</MemoryRouter>,
		);

		const input = document.querySelector(
			'input[type="file"]',
		) as HTMLInputElement;
		const textFile = new File(["notes"], "notes.txt", { type: "text/plain" });

		fireEvent.change(input, { target: { files: [textFile] } });

		expect(screen.getByText("Upload failed")).toBeInTheDocument();
		expect(
			screen.getByText("Only MP4, MOV, and MKV video files are supported."),
		).toBeInTheDocument();
	});

	it("uploads a valid video and shows success state", async () => {
		vi.spyOn(videoService, "uploadVideo").mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "queued",
		});
		const user = userEvent.setup();

		render(
			<MemoryRouter>
				<VideoUploadPanel />
			</MemoryRouter>,
		);

		const input = document.querySelector(
			'input[type="file"]',
		) as HTMLInputElement;
		const videoFile = new File(["video"], "clip.mp4", { type: "video/mp4" });

		await user.upload(input, videoFile);

		await waitFor(() => {
			expect(screen.getByText("Upload complete")).toBeInTheDocument();
		});

		expect(
			screen.getByText("550e8400-e29b-41d4-a716-446655440099"),
		).toBeInTheDocument();
		expect(screen.getByText(/Status: queued/i)).toBeInTheDocument();
	});

	it("shows error when upload fails", async () => {
		vi.spyOn(videoService, "uploadVideo").mockRejectedValue(
			new ValidationError("Could not upload the video."),
		);
		const user = userEvent.setup();

		render(
			<MemoryRouter>
				<VideoUploadPanel />
			</MemoryRouter>,
		);

		const input = document.querySelector(
			'input[type="file"]',
		) as HTMLInputElement;
		const videoFile = new File(["video"], "clip.mp4", { type: "video/mp4" });

		await user.upload(input, videoFile);

		await waitFor(() => {
			expect(screen.getByText("Upload failed")).toBeInTheDocument();
		});

		expect(screen.getByText("Could not upload the video.")).toBeInTheDocument();
	});
});
