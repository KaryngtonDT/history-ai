import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { CITATION_HIGHLIGHT_CLASS } from "@/features/chat/citationNavigation";
import { ProcessingArtifacts } from "@/features/processing/ProcessingArtifacts";
import { artifactService } from "@/services/artifact/ArtifactService";
import { graphService } from "@/services/graph/GraphService";
import { libraryService } from "@/services/library/LibraryService";
import { relationService } from "@/services/relation/RelationService";

const {
	mockAskQuestion,
	mockGetArtifactRelations,
	mockGetArtifactRecommendations,
} = vi.hoisted(() => ({
	mockAskQuestion: vi.fn().mockResolvedValue({
		answer: "Mock answer based on retrieved context.",
		sources: [],
		citations: [],
	}),
	mockGetArtifactRelations: vi.fn(),
	mockGetArtifactRecommendations: vi.fn().mockResolvedValue([]),
}));

vi.mock("@/services/relation/RelationService", () => ({
	relationService: {
		getArtifactRelations: mockGetArtifactRelations,
	},
}));

vi.mock("@/services/graph/GraphService", () => ({
	graphService: {
		getKnowledgeGraph: vi.fn().mockResolvedValue({ nodes: [], edges: [] }),
	},
}));

vi.mock("@/services/recommendation/RecommendationService", () => ({
	recommendationService: {
		getArtifactRecommendations: mockGetArtifactRecommendations,
	},
}));

vi.mock("@/services/semantic/SemanticSearchService", () => ({
	semanticSearchService: {
		searchSemanticChunks: vi.fn().mockResolvedValue([]),
	},
}));

vi.mock("@/services/chat/ChatService", () => ({
	chatService: {
		askQuestion: mockAskQuestion,
	},
}));

describe("ProcessingArtifacts", () => {
	beforeEach(() => {
		mockAskQuestion.mockReset();
		mockAskQuestion.mockResolvedValue({
			answer: "Mock answer based on retrieved context.",
			sources: [],
			citations: [],
		});
		mockGetArtifactRelations.mockReset();
		mockGetArtifactRelations.mockResolvedValue([]);
		mockGetArtifactRecommendations.mockReset();
		mockGetArtifactRecommendations.mockResolvedValue([]);
	});

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
		expect(screen.getByText("Chat unavailable")).toBeInTheDocument();
		expect(
			screen.queryByRole("textbox", { name: "Ask a question" }),
		).not.toBeInTheDocument();
	});

	it("shows chat unavailable while artifacts load for non-uuid content id", () => {
		vi.spyOn(artifactService, "listByContentId").mockReturnValue(
			new Promise(() => {}),
		);

		render(<ProcessingArtifacts contentId="content-1" />);

		expect(screen.getByText("Chat unavailable")).toBeInTheDocument();
		expect(
			screen.queryByRole("textbox", { name: "Ask a question" }),
		).not.toBeInTheDocument();
		expect(
			screen.getByRole("status", { name: "Loading artifacts" }),
		).toBeInTheDocument();
		expect(artifactService.listByContentId).toHaveBeenCalledTimes(1);
		expect(mockAskQuestion).not.toHaveBeenCalled();
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

	it("saves timeline artifact to library from processing page", async () => {
		const user = userEvent.setup();
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "artifact-4",
				contentId: "content-4",
				processingJobId: "job-4",
				type: "timeline",
				content:
					"# Timeline\n\n## Ancient Rome\n\n- 753 BC — Foundation of Rome",
				createdAt: "2026-06-26T12:00:04+00:00",
			},
		]);
		const addItem = vi.spyOn(libraryService, "addItem").mockResolvedValue({
			id: "library-item-4",
			contentId: "content-4",
			artifactId: "artifact-4",
			type: "timeline",
			title: "Timeline",
			createdAt: "2026-06-26T12:00:05+00:00",
		});

		render(<ProcessingArtifacts contentId="content-4" />);

		await waitFor(() => {
			expect(
				screen.getByRole("button", { name: "Save to Library" }),
			).toBeInTheDocument();
		});

		await user.click(screen.getByRole("button", { name: "Save to Library" }));

		await waitFor(() => {
			expect(addItem).toHaveBeenCalledWith({
				contentId: "content-4",
				artifactId: "artifact-4",
				type: "timeline",
				title: "Timeline",
			});
			expect(screen.getByText("Saved to Library")).toBeInTheDocument();
		});
	});

	it("loads artifacts exactly once and passes them to the relations panel", async () => {
		const listByContentId = vi
			.spyOn(artifactService, "listByContentId")
			.mockResolvedValue([
				{
					id: "550e8400-e29b-41d4-a716-446655440002",
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					processingJobId: "job-1",
					type: "summary",
					content: "Generated summary text",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
			]);
		const getArtifactRelations = vi
			.spyOn(relationService, "getArtifactRelations")
			.mockResolvedValue([]);

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await waitFor(() => {
			expect(screen.getByText("Artifact Relations")).toBeInTheDocument();
		});

		expect(listByContentId).toHaveBeenCalledTimes(1);
		expect(getArtifactRelations).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
	});

	it("renders relation rows from RelationService", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "550e8400-e29b-41d4-a716-446655440001",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				processingJobId: "job-1",
				type: "transcript",
				content: "Transcript text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
			{
				id: "550e8400-e29b-41d4-a716-446655440002",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:01+00:00",
			},
		]);
		vi.spyOn(relationService, "getArtifactRelations").mockResolvedValue([
			{
				sourceArtifactId: "550e8400-e29b-41d4-a716-446655440002",
				targetArtifactId: "550e8400-e29b-41d4-a716-446655440001",
				type: "derived_from",
			},
		]);

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("Derived from")).toBeInTheDocument();
		expect(screen.getByRole("link", { name: "Summary" })).toHaveAttribute(
			"href",
			"#artifact-summary",
		);
		expect(screen.getByRole("link", { name: "Transcript" })).toHaveAttribute(
			"href",
			"#artifact-transcript",
		);
	});

	it("renders KnowledgeGraphPanel and still fetches artifacts once", async () => {
		const listByContentId = vi
			.spyOn(artifactService, "listByContentId")
			.mockResolvedValue([
				{
					id: "550e8400-e29b-41d4-a716-446655440002",
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					processingJobId: "job-1",
					type: "summary",
					content: "Generated summary text",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
			]);
		const getKnowledgeGraph = vi
			.spyOn(graphService, "getKnowledgeGraph")
			.mockResolvedValue({
				nodes: [
					{
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						type: "summary",
						title: "Summary",
					},
				],
				edges: [],
			});

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("Knowledge Graph")).toBeInTheDocument();
		expect(
			await screen.findByRole("region", { name: "Knowledge graph" }),
		).toBeInTheDocument();
		expect(listByContentId).toHaveBeenCalledTimes(1);
		expect(getKnowledgeGraph).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
		);
	});

	it("renders SemanticSearchPanel and still fetches artifacts once", async () => {
		const listByContentId = vi
			.spyOn(artifactService, "listByContentId")
			.mockResolvedValue([
				{
					id: "550e8400-e29b-41d4-a716-446655440002",
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					processingJobId: "job-1",
					type: "summary",
					content: "Generated summary text",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
			]);

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("Semantic Search")).toBeInTheDocument();
		expect(
			screen.getByRole("searchbox", { name: "Search query" }),
		).toBeInTheDocument();
		expect(listByContentId).toHaveBeenCalledTimes(1);
	});

	it("renders ChatPanel and still fetches artifacts once", async () => {
		const listByContentId = vi
			.spyOn(artifactService, "listByContentId")
			.mockResolvedValue([
				{
					id: "550e8400-e29b-41d4-a716-446655440002",
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					processingJobId: "job-1",
					type: "summary",
					content: "Generated summary text",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
			]);

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(
			await screen.findByText("Generated summary text"),
		).toBeInTheDocument();
		expect(screen.getByText("Chat with this document")).toBeInTheDocument();
		expect(
			screen.getByRole("textbox", { name: "Ask a question" }),
		).toBeInTheDocument();
		expect(listByContentId).toHaveBeenCalledTimes(1);
	});

	it("derives chat content id from artifact uuid when prop is not a uuid", async () => {
		const user = userEvent.setup();
		const chatContentId = "550e8400-e29b-41d4-a716-446655440001";

		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "550e8400-e29b-41d4-a716-446655440002",
				contentId: chatContentId,
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);

		render(<ProcessingArtifacts contentId="1" />);

		expect(
			await screen.findByText("Generated summary text"),
		).toBeInTheDocument();
		expect(screen.getByText("Chat with this document")).toBeInTheDocument();

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"what is the purpose?{enter}",
		);

		await waitFor(() => {
			expect(mockAskQuestion).toHaveBeenCalledWith(
				chatContentId,
				"what is the purpose?",
			);
		});
	});

	it("shows chat unavailable for non-uuid content id without calling ChatService", async () => {
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

		expect(
			await screen.findByText("Generated summary text"),
		).toBeInTheDocument();
		expect(screen.getByText("Chat unavailable")).toBeInTheDocument();
		expect(
			screen.queryByRole("textbox", { name: "Ask a question" }),
		).not.toBeInTheDocument();
		expect(mockAskQuestion).not.toHaveBeenCalled();
	});

	it("loads artifacts exactly once when recommendations panels are shown", async () => {
		const listByContentId = vi
			.spyOn(artifactService, "listByContentId")
			.mockResolvedValue([
				{
					id: "550e8400-e29b-41d4-a716-446655440002",
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					processingJobId: "job-1",
					type: "summary",
					content: "Generated summary text",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
				{
					id: "550e8400-e29b-41d4-a716-446655440003",
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					processingJobId: "job-1",
					type: "quiz",
					content: "# Quiz\n\n## Question 1\nQuiz question\nAnswer: A",
					createdAt: "2026-06-26T12:00:02+00:00",
				},
			]);
		const getArtifactRecommendations = mockGetArtifactRecommendations;

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await waitFor(() => {
			expect(getArtifactRecommendations).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
				"550e8400-e29b-41d4-a716-446655440002",
			);
			expect(getArtifactRecommendations).toHaveBeenCalledWith(
				"550e8400-e29b-41d4-a716-446655440000",
				"550e8400-e29b-41d4-a716-446655440003",
			);
		});

		expect(listByContentId).toHaveBeenCalledTimes(1);
	});

	it("does not show recommendations for missing artifact placeholders", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "550e8400-e29b-41d4-a716-446655440002",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);
		const getArtifactRecommendations = mockGetArtifactRecommendations;

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		await waitFor(() => {
			expect(screen.getByText("Generated summary text")).toBeInTheDocument();
			expect(screen.getByText("No quiz yet")).toBeInTheDocument();
		});

		expect(screen.getAllByText("See also")).toHaveLength(1);

		await waitFor(() => {
			expect(getArtifactRecommendations).toHaveBeenCalledTimes(1);
		});
		expect(getArtifactRecommendations).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440002",
		);
	});

	it("renders recommendation links from RecommendationService", async () => {
		vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
			{
				id: "550e8400-e29b-41d4-a716-446655440002",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				processingJobId: "job-1",
				type: "summary",
				content: "Generated summary text",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
			{
				id: "550e8400-e29b-41d4-a716-446655440003",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				processingJobId: "job-1",
				type: "quiz",
				content: "# Quiz\n\n## Question 1\nQuiz question\nAnswer: A",
				createdAt: "2026-06-26T12:00:02+00:00",
			},
		]);
		mockGetArtifactRecommendations.mockImplementation(
			(_contentId, artifactId) => {
				if (artifactId === "550e8400-e29b-41d4-a716-446655440002") {
					return Promise.resolve([
						{
							artifactId: "550e8400-e29b-41d4-a716-446655440003",
							type: "quiz",
							title: "Practice Quiz",
							reason: "next",
						},
					]);
				}

				return Promise.resolve([]);
			},
		);

		render(
			<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
		);

		expect(await screen.findByText("Practice Quiz")).toBeInTheDocument();
		expect(screen.getByRole("link", { name: /Practice Quiz/ })).toHaveAttribute(
			"href",
			"#artifact-quiz",
		);
		expect(screen.getByText("Next")).toBeInTheDocument();
	});

	it("scrolls and highlights artifact when a citation marker is clicked", async () => {
		vi.useFakeTimers({ shouldAdvanceTime: true });

		try {
			const user = userEvent.setup({ advanceTimers: vi.advanceTimersByTime });
			const summaryId = "550e8400-e29b-41d4-a716-446655440002";
			const chunkId = "550e8400-e29b-41d4-a716-446655440010";

			vi.spyOn(artifactService, "listByContentId").mockResolvedValue([
				{
					id: summaryId,
					contentId: "550e8400-e29b-41d4-a716-446655440000",
					processingJobId: "job-1",
					type: "summary",
					content: "Generated summary text",
					createdAt: "2026-06-26T12:00:00+00:00",
				},
			]);
			mockAskQuestion.mockResolvedValue({
				answer: "Rome collapsed because of military pressure [1].",
				sources: [
					{
						artifactId: summaryId,
						chunkId,
						text: "Generated summary text",
						score: 0.91,
					},
				],
				citations: [
					{
						number: 1,
						artifactId: summaryId,
						chunkId,
						score: 0.91,
					},
				],
			});

			render(
				<ProcessingArtifacts contentId="550e8400-e29b-41d4-a716-446655440000" />,
			);

			expect(
				await screen.findByText("Generated summary text"),
			).toBeInTheDocument();

			const artifactTarget = document.getElementById("artifact-summary");
			expect(artifactTarget).not.toBeNull();
			const scrollIntoView = vi.fn();
			if (artifactTarget !== null) {
				artifactTarget.scrollIntoView = scrollIntoView;
			}

			await user.type(
				screen.getByRole("textbox", { name: "Ask a question" }),
				"Why Rome?{enter}",
			);

			await user.click(
				await screen.findByRole("button", { name: "Citation 1" }),
			);

			expect(scrollIntoView).toHaveBeenCalledWith({
				behavior: "smooth",
				block: "start",
			});
			expect(artifactTarget?.classList.contains(CITATION_HIGHLIGHT_CLASS)).toBe(
				true,
			);

			vi.advanceTimersByTime(3000);

			expect(artifactTarget?.classList.contains(CITATION_HIGHLIGHT_CLASS)).toBe(
				false,
			);
		} finally {
			vi.useRealTimers();
		}
	});
});
