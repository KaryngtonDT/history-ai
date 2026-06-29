import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import type { Conversation } from "@/services/conversation/types";
import { ChatPanel } from "./ChatPanel";

const { mockStreamQuestion, mockUpdateDocuments } = vi.hoisted(() => ({
	mockStreamQuestion: vi.fn(),
	mockUpdateDocuments: vi.fn(),
}));

vi.mock("@/services/conversation/ConversationService", () => ({
	conversationService: {
		streamQuestion: mockStreamQuestion,
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

function mockStreamResponse(
	question: string,
	answerText = "Mock answer based on retrieved context.",
	previousMessages: Array<{ role: "user" | "assistant"; text: string }> = [],
	documents: Array<{ contentId: string }> = [{ contentId }],
): void {
	mockStreamQuestion.mockImplementationOnce(
		async (
			_streamContentId: string,
			_streamConversationId: string,
			streamQuestion: string,
			callbacks: {
				onToken: (token: { index: number; text: string }) => void;
				onConversation: (conversation: Conversation) => void;
				onDone: () => void;
				onError: (error: Error) => void;
			},
		) => {
			const tokens = [
				"Mock ",
				"answer ",
				"based ",
				"on ",
				"retrieved ",
				"context.",
			];
			for (const [index, text] of tokens.entries()) {
				callbacks.onToken({ index, text });
			}

			callbacks.onConversation({
				id: conversationId,
				contentId,
				messages: [
					...previousMessages,
					{ role: "user", text: streamQuestion },
					{ role: "assistant", text: answerText },
				],
				documents,
			});
			callbacks.onDone();
		},
	);

	void question;
}

describe("ChatPanel", () => {
	beforeEach(() => {
		mockStreamQuestion.mockReset();
		mockUpdateDocuments.mockReset();
		vi.spyOn(crypto, "randomUUID").mockReturnValue(conversationId);
	});

	it("creates a conversation on first message", async () => {
		const user = userEvent.setup();
		mockStreamResponse("Why did Rome collapse?");

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await waitFor(() => {
			expect(mockStreamQuestion).toHaveBeenCalledWith(
				contentId,
				conversationId,
				"Why did Rome collapse?",
				expect.objectContaining({
					onToken: expect.any(Function),
					onConversation: expect.any(Function),
					onDone: expect.any(Function),
					onError: expect.any(Function),
				}),
			);
		});

		expect(screen.getByText("Why did Rome collapse?")).toBeInTheDocument();
		expect(
			screen.getByText("Mock answer based on retrieved context."),
		).toBeInTheDocument();
	});

	it("reuses the same conversation id for subsequent questions", async () => {
		const user = userEvent.setup();
		mockStreamResponse("First question");
		mockStreamResponse(
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
			expect(mockStreamQuestion).toHaveBeenNthCalledWith(
				2,
				contentId,
				conversationId,
				"Second question",
				expect.any(Object),
			);
		});

		expect(screen.getByText("First question")).toBeInTheDocument();
		expect(screen.getByText("Second question")).toBeInTheDocument();
	});

	it("renders messages from the server conversation payload", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockImplementationOnce(async (_c, _i, _q, callbacks) => {
			callbacks.onToken({ index: 0, text: "Server " });
			callbacks.onConversation({
				id: conversationId,
				contentId,
				messages: [
					{ role: "user", text: "Server question" },
					{ role: "assistant", text: "Server answer" },
				],
				documents: [{ contentId }],
			});
			callbacks.onDone();
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
		mockStreamResponse("Why did Rome collapse?");

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?{enter}",
		);

		await waitFor(() => {
			expect(mockStreamQuestion).toHaveBeenCalledWith(
				contentId,
				conversationId,
				"Why did Rome collapse?",
				expect.any(Object),
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
		expect(mockStreamQuestion).not.toHaveBeenCalled();
	});

	it("disables submit while streaming", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockReturnValue(new Promise(() => {}));

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

	it("shows error state when conversation stream rejects", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockRejectedValue(new Error("Network error"));

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			await screen.findByText("Unable to get an answer"),
		).toBeInTheDocument();
		expect(screen.getByText("Unable to generate answer.")).toBeInTheDocument();
	});

	it("shows stream fallback when conversation event is missing", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockImplementationOnce(async (_c, _i, _q, callbacks) => {
			callbacks.onToken({ index: 0, text: "Partial " });
			callbacks.onDone();
		});

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			await screen.findByText("Unable to generate answer."),
		).toBeInTheDocument();
	});

	it("forwards citation clicks through onCitationClick prop", async () => {
		const user = userEvent.setup();
		const onCitationClick = vi.fn();
		mockStreamQuestion.mockImplementationOnce(async (_c, _i, _q, callbacks) => {
			callbacks.onToken({ index: 0, text: "Rome collapsed" });
			callbacks.onConversation({
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
			});
			callbacks.onDone();
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
		expect(onCitationClick).not.toHaveBeenCalled();
	});

	it("uses ConversationService.streamQuestion and not fetch or HTTP repositories", () => {
		const source = readFileSync(join(__dirname, "ChatPanel.tsx"), "utf8");
		const fetchPattern = ["fetch", "("].join("");

		expect(source).toContain(".streamQuestion(");
		expect(source).not.toContain(fetchPattern);
		expect(source).not.toContain("HttpConversationRepository");
		expect(source).not.toContain("ConversationRepositoryFactory");
		expect(source).not.toContain("HttpChatRepository");
	});

	it("shows document selector after the first conversation exists", async () => {
		const user = userEvent.setup();
		mockStreamResponse("Why did Rome collapse?");

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
		mockStreamResponse("Why did Rome collapse?");
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

		expect(mockStreamQuestion).toHaveBeenCalledTimes(1);
		expect(screen.getByLabelText(otherContentId)).toBeChecked();
	});
});
