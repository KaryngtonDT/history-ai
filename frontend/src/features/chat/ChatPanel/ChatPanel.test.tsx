import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { beforeEach, describe, expect, it, vi } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import { ChatPanel } from "./ChatPanel";

const { mockAskQuestion } = vi.hoisted(() => ({
	mockAskQuestion: vi.fn(),
}));

vi.mock("@/services/chat/ChatService", () => ({
	chatService: {
		askQuestion: mockAskQuestion,
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

describe("ChatPanel", () => {
	beforeEach(() => {
		mockAskQuestion.mockReset();
	});

	it("calls ChatService when Send is clicked", async () => {
		const user = userEvent.setup();
		mockAskQuestion.mockResolvedValue({
			answer: "Mock answer based on retrieved context.",
			sources: [
				{
					artifactId: "550e8400-e29b-41d4-a716-446655440002",
					chunkId: "550e8400-e29b-41d4-a716-446655440010",
					text: "## Ancient Rome",
					score: 0.97,
				},
			],
		});

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		await waitFor(() => {
			expect(mockAskQuestion).toHaveBeenCalledWith(
				contentId,
				"Why did Rome collapse?",
			);
		});

		expect(screen.getByText("Why did Rome collapse?")).toBeInTheDocument();
		expect(
			screen.getByText("Mock answer based on retrieved context."),
		).toBeInTheDocument();
		expect(
			screen.getByRole("link", { name: "Summary (0.97)" }),
		).toBeInTheDocument();
	});

	it("submits on Enter key", async () => {
		const user = userEvent.setup();
		mockAskQuestion.mockResolvedValue({
			answer: "Mock answer based on retrieved context.",
			sources: [],
		});

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?{enter}",
		);

		await waitFor(() => {
			expect(mockAskQuestion).toHaveBeenCalledWith(
				contentId,
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

	it("shows loading spinner while waiting for answer", async () => {
		const user = userEvent.setup();
		mockAskQuestion.mockReturnValue(new Promise(() => {}));

		render(<ChatPanel contentId={contentId} artifacts={artifacts} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"Why did Rome collapse?",
		);
		await user.click(screen.getByRole("button", { name: "Send" }));

		expect(
			screen.getByRole("status", { name: "Generating answer" }),
		).toBeInTheDocument();
	});

	it("shows error state when ChatService fails", async () => {
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
	});

	it("does not use direct fetch or HTTP repository imports", () => {
		const source = readFileSync(join(__dirname, "ChatPanel.tsx"), "utf8");
		const fetchPattern = ["fetch", "("].join("");

		expect(source).not.toContain(fetchPattern);
		expect(source).not.toContain("HttpChatRepository");
		expect(source).not.toContain("ChatRepositoryFactory");
	});
});
