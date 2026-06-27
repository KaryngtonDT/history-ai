import { render, screen, waitFor } from "@testing-library/react";
import { afterEach, describe, expect, it, vi } from "vitest";
import { ProcessingArtifacts } from "@/features/processing/ProcessingArtifacts";
import { artifactService } from "@/services/artifact/ArtifactService";

describe("ProcessingArtifacts", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});
	it("displays summary artifact content in a card", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-1",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Generated summary text")).toBeInTheDocument();
		});
		expect(screen.getByText("Summary")).toBeInTheDocument();
	});

	it("displays transcript artifact content in a scrollable card", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-2",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "transcript",
				content: "The Roman Empire was a vast civilization.",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(
				screen.getByText("The Roman Empire was a vast civilization."),
			).toBeInTheDocument();
		});
		expect(screen.getByText("Transcript")).toBeInTheDocument();
	});

	it("displays quiz artifact content in a card", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-3",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "quiz",
				content:
					"# Quiz\n\n## Question 1\nSample question\n- A) One\nAnswer: A",
				createdAt: "2026-06-26T12:00:02+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Quiz")).toBeInTheDocument();
			expect(
				screen.getByRole("heading", { name: "Question 1" }),
			).toBeInTheDocument();
		});
	});

	it("displays summary, transcript and quiz when all are present", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-2",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "transcript",
				content: "Extracted transcript text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
			{
				id: "artifact-1",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:01+00:00",
			},
			{
				id: "artifact-3",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "quiz",
				content: "# Quiz\n\n## Question 1\nQuiz question\nAnswer: A",
				createdAt: "2026-06-26T12:00:02+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Generated summary text")).toBeInTheDocument();
			expect(screen.getByText("Extracted transcript text")).toBeInTheDocument();
			expect(
				screen.getByRole("heading", { name: "Question 1" }),
			).toBeInTheDocument();
		});
	});

	it("shows quiz empty state when summary and transcript exist without quiz", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-1",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
			{
				id: "artifact-2",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "transcript",
				content: "Extracted transcript text",
				createdAt: "2026-06-26T12:00:01+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Generated summary text")).toBeInTheDocument();
			expect(screen.getByText("No quiz yet")).toBeInTheDocument();
		});
	});

	it("shows empty state when no artifacts exist", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("No artifacts yet")).toBeInTheDocument();
		});
	});

	it("loads artifacts with a single API request", async () => {
		const listSpy = vi
			.spyOn(artifactService, "listByContentId")
			.mockResolvedValue([
				{
					id: "artifact-1",
					contentId: "content-1",
					processingJobId: "job-1",
					type: "summary",
					content: "Generated summary text",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
				{
					id: "artifact-3",
					contentId: "content-1",
					processingJobId: "job-1",
					type: "quiz",
					content: "# Quiz\n\n## Question 1\nQuiz question",
					createdAt: "2026-06-26T12:00:02+00:00",
				},
			]);

		try {
			render(<ProcessingArtifacts contentId="content-1" />);

			await waitFor(() => {
				expect(screen.getByText("Generated summary text")).toBeInTheDocument();
			});

			expect(listSpy).toHaveBeenCalledTimes(1);
			expect(listSpy).toHaveBeenCalledWith("content-1");
		} finally {
			listSpy.mockRestore();
		}
	});
});
