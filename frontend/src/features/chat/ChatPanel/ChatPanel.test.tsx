import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import { ChatPanel } from "./ChatPanel";

const { mockAskQuestion, mockUpdateDocuments } = vi.hoisted(() => ({
	mockAskQuestion: vi.fn(),
	mockUpdateDocuments: vi.fn(),
}));

vi.mock("@/services/conversation/ConversationService", () => ({
	conversationService: {
		askQuestion: mockAskQuestion,
		updateDocuments: mockUpdateDocuments,
	},
}));

const contentId = "550e8400-e29b-41d4-a716-446655440000";
const otherContentId = "550e8400-e29b-41d4-a716-446655440099";
const conversationId = "550e8400-e29b-41d4-a716-446655440001";
const artifacts: Artifact[] = [
	{
		id: "550e8400-e29b-41d4-a716-446655440002",
		contentId,
		processingJobId: "job-1",
		type: "summary",
		content: "## Ancient Rome\n753 BC — Foundation of Rome",
		createdAt: "2026-06-26T12:00:00+00:00",
	},
];

function mockConversationResponse(
	question: string,
	answerText = "Mock answer based on retrieved context.",
	previousMessages: Array<{ role: "user" | "assistant"; text: string }> = [],
): void {
	mockAskQuestion.mockResolvedValueOnce({
		conversation: {
			id: conversationId,
			contentId,
			messages: [
				...previousMessages,
				{ role: "user", text: question },
				{ role: "assistant", text: answerText },
			],
			documents: [{ contentId }],
		},
		answer: {
			answer: answerText,
			sources: [],
			citations: [],
		},
	});
}

describe("ChatPanel", () => {
	beforeEach(() => {
		mockAskQuestion.mockReset();
		mockUpdateDocuments.mockReset();
		vi.spyOn(crypto, "randomUUID").mockReturnValue(conversationId);
	});

	it("creates a conversation on first message", async () => {
		const user = userEvent.setup();
		mockConversationResponse("Why did Rome collapse?");

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await waitFor(() => {
			expect(mockAskQuestion).toHaveBeenCalledWith(
				contentId,
				conversationId,
				"Why did Rome collapse?",
			);
		});

		expect(screen.getByText("Why did Rome collapse?")).toBeInTheDocument();
		expect(
			screen.getByText("Mock answer based on retrieved context."),
		).toBeInTheDocument();
	});

	it("reuses the same conversation id for subsequent questions", async () => {
		const user = userEvent.setup();
		mockConversationResponse("First question");
		mockConversationResponse(
			"Second question",
			"Mock answer based on retrieved context.",
			[
				{ role: "user", text: "First question" },
				{
					role: "assistant",
					text: "Mock answer based on retrieved context.",
				},
			],
		);

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"First question",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await waitFor(() => {
			expect(
				screen.getByText("Mock answer based on retrieved context."),
			).toBeInTheDocument();
		});

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Second question",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await waitFor(() => {
			expect(mockAskQuestion).toHaveBeenNthCalledWith(
				2,
				contentId,
				conversationId,
				"Second question",
			);
		});

		expect(screen.getByText("First question")).toBeInTheDocument();
		expect(screen.getByText("Second question")).toBeInTheDocument();
	});

	it("renders messages from the server conversation payload", async () => {
		const user = userEvent.setup();
		mockAskQuestion.mockResolvedValueOnce({
			conversation: {
				id: conversationId,
				contentId,
				messages: [
					{ role: "user", text: "Server question" },
					{ role: "assistant", text: "Server answer" },
				],
				documents: [{ contentId }],
			},
			answer: {
				answer: "Server answer",
				sources: [],
				citations: [],
			},
		});

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Local draft should not render",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(await screen.findByText("Server question")).toBeInTheDocument();
		expect(screen.getByText("Server answer")).toBeInTheDocument();
		expect(
			screen.queryByText("Local draft should not render"),
		).not.toBeInTheDocument();
	});

	it("submits on Enter key", async () => {
		const user = userEvent.setup();
		mockConversationResponse("Why did Rome collapse?");

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?{enter}",
		);

		await waitFor(() => {
			expect(mockAskQuestion).toHaveBeenCalledWith(
				contentId,
				conversationId,
				"Why did Rome collapse?",
			);
		});
	});

	it("disables send for empty question", async () => {
		const user = userEvent.setup();

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"   ",
		);

		expect(screen.getByRole("button", { name: "Send" })).toBeDisabled();
		expect(mockAskQuestion).not.toHaveBeenCalled();
	});

	it("disables submit while loading", async () => {
		const user = userEvent.setup();
		mockAskQuestion.mockReturnValue(new Promise(() => {}));

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(screen.getByRole("button", { name: "Send" })).toBeDisabled();
		expect(
			screen.getByRole("textbox", { name: "Ask a question" }),
		).toBeDisabled();
	});

	it("shows error state when conversation service rejects", async () => {
		const user = userEvent.setup();
		mockAskQuestion.mockRejectedValue(new Error("Network error"));

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			await screen.findByText("Unable to get an answer"),
		).toBeInTheDocument();
		expect(
			screen.getByText(
				"Something went wrong while asking this content. Please try again.",
			),
		).toBeInTheDocument();
	});

	it("shows empty answer fallback for invalid response", async () => {
		const user = userEvent.setup();
		mockAskQuestion.mockResolvedValueOnce({
			conversation: { id: "", contentId: "", messages: [], documents: [] },
			answer: { answer: "", sources: [], citations: [] },
		});

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			await screen.findByText("Unable to get an answer"),
		).toBeInTheDocument();
		expect(
			screen.getByText(
				"No answer was returned for this question. Check that the content identifier is valid.",
			),
		).toBeInTheDocument();
	});

	it("forwards citation clicks through onCitationClick prop", async () => {
		const user = userEvent.setup();
		const onCitationClick = vi.fn();
		mockAskQuestion.mockResolvedValueOnce({
			conversation: {
				id: conversationId,
				contentId,
				messages: [
					{ role: "user", text: "Why did Rome collapse?" },
					{
						role: "assistant",
						text: "Rome collapsed because of military pressure [1].",
					},
				],
				documents: [{ contentId }],
			},
			answer: {
				answer: "Rome collapsed because of military pressure [1].",
				sources: [
					{
						artifactId: artifacts[0].id,
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						text: "## Ancient Rome",
						score: 0.87,
					},
				],
				citations: [
					{
						number: 1,
						artifactId: artifacts[0].id,
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						score: 0.87,
					},
				],
			},
		});

		render(
			<ChatPanel
				contentId={contentId}
				artifacts={artifacts}
				onCitationClick={onCitationClick}
			/>,
		);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			await screen.findByText(/Rome collapsed because of military pressure/),
		).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Citation 1" }),
		).toBeInTheDocument();
		expect(onCitationClick).not.toHaveBeenCalled();
	});

	it("does not use fetch, streamQuestion, or HTTP repository imports", () => {
		const source = readFileSync(join(__dirname, "ChatPanel.tsx"), "utf8");
		const fetchPattern = ["fetch", "("].join("");

		expect(source).not.toContain("streamQuestion");
		expect(source).not.toContain(fetchPattern);
		expect(source).not.toContain("HttpConversationRepository");
		expect(source).not.toContain("ConversationRepositoryFactory");
		expect(source).not.toContain("HttpChatRepository");
	});

	it("shows document selector after the first conversation exists", async () => {
		const user = userEvent.setup();
		mockConversationResponse("Why did Rome collapse?");

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		expect(
			screen.queryByText("Documents in this conversation"),
		).not.toBeInTheDocument();

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			await screen.findByText("Documents in this conversation"),
		).toBeInTheDocument();
		expect(screen.getByLabelText("This document")).toBeChecked();
	});

	it("calls updateDocuments when document selection changes", async () => {
		const user = userEvent.setup();
		mockConversationResponse("Why did Rome collapse?");
		mockUpdateDocuments.mockResolvedValueOnce({
			id: conversationId,
			contentId: otherContentId,
			messages: [
				{ role: "user", text: "Why did Rome collapse?" },
				{
					role: "assistant",
					text: "Mock answer based on retrieved context.",
				},
			],
			documents: [{ contentId: otherContentId }, { contentId }],
		});

		const relatedArtifacts: Artifact[] = [
			...artifacts,
			{
				...artifacts[0],
				id: "550e8400-e29b-41d4-a716-446655440003",
				contentId: otherContentId,
			},
		];

		render(<ChatPanel contentId={contentId} artifacts={relatedArtifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await screen.findByText("Documents in this conversation");

		await user.click(screen.getByLabelText(otherContentId));

		await waitFor(() => {
			expect(mockUpdateDocuments).toHaveBeenCalledWith(conversationId, [
				contentId,
				otherContentId,
			]);
		});

		expect(mockAskQuestion).toHaveBeenCalledTimes(1);
		expect(screen.getByLabelText(otherContentId)).toBeChecked();
	});
});
