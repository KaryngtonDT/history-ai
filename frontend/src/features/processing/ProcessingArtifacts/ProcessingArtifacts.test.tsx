import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, describe, expect, it, vi } from "vitest";
import { ProcessingArtifacts } from "@/features/processing/ProcessingArtifacts";
import { artifactService } from "@/services/artifact/ArtifactService";
import { libraryService } from "@/services/library/LibraryService";

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

	it("displays flashcards artifact content in a card", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-4",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "flashcards",
				content:
					"# Flashcards\n\n## Card 1\n\nFront:\nSample term\n\nBack:\nSample definition",
				createdAt: "2026-06-26T12:00:03+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Flashcards")).toBeInTheDocument();
			expect(
				screen.getByRole("heading", { name: "Card 1" }),
			).toBeInTheDocument();
			expect(screen.getByText("Sample term")).toBeInTheDocument();
			expect(screen.getByText("Sample definition")).toBeInTheDocument();
		});
	});

	it("displays timeline artifact content in a card", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-5",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "timeline",
				content:
					"# Timeline\n\n## Ancient Rome\n\n- 753 BC — Foundation of Rome\n- Republic established",
				createdAt: "2026-06-26T12:00:04+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Timeline")).toBeInTheDocument();
			expect(
				screen.getByRole("heading", { name: "Ancient Rome" }),
			).toBeInTheDocument();
			expect(
				screen.getByText("753 BC — Foundation of Rome"),
			).toBeInTheDocument();
			expect(screen.getByText("Republic established")).toBeInTheDocument();
		});
	});

	it("displays summary, transcript, quiz, flashcards and timeline when all are present", async () => {
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
			{
				id: "artifact-4",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "flashcards",
				content:
					"# Flashcards\n\n## Card 1\n\nFront:\nTerm\n\nBack:\nDefinition",
				createdAt: "2026-06-26T12:00:03+00:00",
			},
			{
				id: "artifact-5",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "timeline",
				content:
					"# Timeline\n\n## Ancient Rome\n\n- 753 BC — Foundation of Rome",
				createdAt: "2026-06-26T12:00:04+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Generated summary text")).toBeInTheDocument();
			expect(screen.getByText("Extracted transcript text")).toBeInTheDocument();
			expect(
				screen.getByRole("heading", { name: "Question 1" }),
			).toBeInTheDocument();
			expect(
				screen.getByRole("heading", { name: "Card 1" }),
			).toBeInTheDocument();
			expect(screen.getByText("Term")).toBeInTheDocument();
			expect(screen.getByText("Definition")).toBeInTheDocument();
			expect(
				screen.getByRole("heading", { name: "Ancient Rome" }),
			).toBeInTheDocument();
			expect(
				screen.getByText("753 BC — Foundation of Rome"),
			).toBeInTheDocument();
		});
	});

	it("shows timeline empty state when other artifacts exist without timeline", async () => {
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
			{
				id: "artifact-3",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "quiz",
				content: "# Quiz\n\n## Question 1\nQuiz question\nAnswer: A",
				createdAt: "2026-06-26T12:00:02+00:00",
			},
			{
				id: "artifact-4",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "flashcards",
				content:
					"# Flashcards\n\n## Card 1\n\nFront:\nTerm\n\nBack:\nDefinition",
				createdAt: "2026-06-26T12:00:03+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("Generated summary text")).toBeInTheDocument();
			expect(screen.getByText("No timeline yet")).toBeInTheDocument();
		});
	});

	it("shows flashcards empty state when other artifacts exist without flashcards", async () => {
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
			expect(screen.getByText("No flashcards yet")).toBeInTheDocument();
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

	it("renders unsupported artifact types with fallback card", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-podcast-1",
				contentId: "content-1",
				processingJobId: "job-1",
				type: "podcast",
				content: "Podcast preview content",
				createdAt: "2026-06-26T12:00:05+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(screen.getByText("podcast")).toBeInTheDocument();
			expect(
				screen.getByText(
					"This artifact type is not yet supported in the viewer.",
				),
			).toBeInTheDocument();
			expect(screen.getByText("Podcast preview content")).toBeInTheDocument();
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

	it("shows save to library action for generated artifacts", async () => {
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
			expect(
				screen.getByRole("button", { name: "Save to Library" }),
			).toBeInTheDocument();
		});
	});

	it("saves artifact to library from processing page", async () => {
		const user = userEvent.setup();
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
		const addItem = vi.spyOn(libraryService, "addItem").mockResolvedValue({
			id: "library-item-1",
			contentId: "content-1",
			artifactId: "artifact-1",
			type: "summary",
			title: "Summary",
			createdAt: "2026-06-26T12:00:01+00:00",
		});

		render(<ProcessingArtifacts contentId="content-1" />);

		await waitFor(() => {
			expect(
				screen.getByRole("button", { name: "Save to Library" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Save to Library" }));

		await waitFor(() => {
			expect(addItem).toHaveBeenCalledWith({
				contentId: "content-1",
				artifactId: "artifact-1",
				type: "summary",
				title: "Summary",
			});
			expect(screen.getByText("Saved to Library")).toBeInTheDocument();
		});
	});
});
