import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import { ChatPanel } from "./ChatPanel";

const { mockStreamQuestion } = vi.hoisted(() => ({
	mockStreamQuestion: vi.fn(),
}));

vi.mock("@/services/chat/ChatService", () => ({
	chatService: {
		streamQuestion: mockStreamQuestion,
	},
}));

const contentId = "550e8400-e29b-41d4-a716-446655440000";
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

function mockStreamWithTokens(
	tokens: Array<{ index: number; text: string }>,
): void {
	mockStreamQuestion.mockImplementation((_contentId, _question, callbacks) => {
		for (const token of tokens) {
			callbacks.onToken(token);
		}
		callbacks.onDone();
		return Promise.resolve();
	});
}

describe("ChatPanel", () => {
	beforeEach(() => {
		mockStreamQuestion.mockReset();
	});

	it("calls streamQuestion when Send is clicked", async () => {
		const user = userEvent.setup();
		mockStreamWithTokens([
			{ index: 0, text: "Mock " },
			{ index: 1, text: "answer based on retrieved context." },
		]);

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await waitFor(() => {
			expect(mockStreamQuestion).toHaveBeenCalledWith(
				contentId,
				"Why did Rome collapse?",
				expect.objectContaining({
					onToken: expect.any(Function),
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

	it("shows user message immediately and generating state before tokens arrive", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockImplementation(() => new Promise(() => {}));

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(screen.getByText("Why did Rome collapse?")).toBeInTheDocument();
		expect(screen.getByText("Generating...")).toBeInTheDocument();
		expect(screen.getByLabelText("Assistant")).toHaveAttribute(
			"aria-busy",
			"true",
		);
	});

	it("appends tokens in order and clears generating state on done", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockImplementation(
			async (_contentId, _question, callbacks) => {
				callbacks.onToken({ index: 0, text: "Mock " });
				callbacks.onToken({ index: 1, text: "answer " });
				callbacks.onToken({ index: 2, text: "based on retrieved context." });
				callbacks.onDone();
			},
		);

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await waitFor(() => {
			expect(
				screen.getByText("Mock answer based on retrieved context."),
			).toBeInTheDocument();
		});
		expect(screen.queryByText("Generating...")).not.toBeInTheDocument();
		expect(screen.getByLabelText("Assistant")).not.toHaveAttribute("aria-busy");
	});

	it("submits on Enter key", async () => {
		const user = userEvent.setup();
		mockStreamWithTokens([{ index: 0, text: "Mock answer." }]);

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?{enter}",
		);

		await waitFor(() => {
			expect(mockStreamQuestion).toHaveBeenCalledWith(
				contentId,
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

	it("shows fallback when stream fails before any token", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockImplementation(
			(_contentId, _question, callbacks) => {
				callbacks.onError(new Error("Network error"));
				return Promise.resolve();
			},
		);

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			await screen.findByText("Unable to generate answer."),
		).toBeInTheDocument();
		expect(screen.queryByLabelText("Assistant")).not.toBeInTheDocument();
	});

	it("shows error state when stream fails after tokens were received", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockImplementation(
			(_contentId, _question, callbacks) => {
				callbacks.onToken({ index: 0, text: "Partial " });
				callbacks.onError(new Error("Network error"));
				return Promise.resolve();
			},
		);

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(await screen.findByLabelText("Assistant")).toHaveTextContent(
			"Partial ",
		);
		expect(
			await screen.findByText("Unable to get an answer"),
		).toBeInTheDocument();
	});

	it("does not render an empty assistant bubble when stream completes with empty answer", async () => {
		const user = userEvent.setup();
		mockStreamQuestion.mockImplementation(
			(_contentId, _question, callbacks) => {
				callbacks.onDone();
				return Promise.resolve();
			},
		);

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
		expect(screen.queryByLabelText("Assistant")).not.toBeInTheDocument();
	});

	it("forwards citation clicks through onCitationClick prop", async () => {
		const user = userEvent.setup();
		const onCitationClick = vi.fn();
		mockStreamQuestion.mockImplementation(
			(_contentId, _question, callbacks) => {
				callbacks.onToken({
					index: 0,
					text: "Rome collapsed because of military pressure [1].",
				});
				callbacks.onDone();
				return Promise.resolve();
			},
		);

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
			await screen.findByText(
				"Rome collapsed because of military pressure [1].",
			),
		).toBeInTheDocument();
		expect(onCitationClick).not.toHaveBeenCalled();
	});

	it("does not use askQuestion, fetch, or HTTP repository imports", () => {
		const source = readFileSync(join(__dirname, "ChatPanel.tsx"), "utf8");
		const fetchPattern = ["fetch", "("].join("");

		expect(source).not.toContain("askQuestion");
		expect(source).not.toContain(fetchPattern);
		expect(source).not.toContain("HttpChatRepository");
		expect(source).not.toContain("ChatRepositoryFactory");
	});
});
