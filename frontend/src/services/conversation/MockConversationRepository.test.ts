import { describe, expect, it, vi } from "vitest";
import { MockConversationRepository } from "./MockConversationRepository";

const contentId = "550e8400-e29b-41d4-a716-446655440000";
const conversationId = "550e8400-e29b-41d4-a716-446655440001";

describe("MockConversationRepository", () => {
	it("creates a conversation on first question", async () => {
		const repository = new MockConversationRepository();

		const result = await repository.askQuestion(
			contentId,
			conversationId,
			"Why did Rome collapse?",
		);

		expect(result.conversation.id).toBe(conversationId);
		expect(result.conversation.contentId).toBe(contentId);
		expect(result.conversation.messages).toEqual([
			{ role: "user", text: "Why did Rome collapse?" },
			{
				role: "assistant",
				text: result.answer.answer,
			},
		]);
	});

	it("appends to an existing conversation", async () => {
		const repository = new MockConversationRepository();

		await repository.askQuestion(contentId, conversationId, "First question");
		const result = await repository.askQuestion(
			contentId,
			conversationId,
			"Second question",
		);

		expect(result.conversation.messages).toHaveLength(4);
		expect(result.conversation.messages[0]).toEqual({
			role: "user",
			text: "First question",
		});
		expect(result.conversation.messages[2]).toEqual({
			role: "user",
			text: "Second question",
		});
	});

	it("updates selected documents while preserving messages", async () => {
		const repository = new MockConversationRepository();
		const otherContentId = "550e8400-e29b-41d4-a716-446655440099";

		await repository.askQuestion(contentId, conversationId, "First question");
		const updated = await repository.updateDocuments(conversationId, [
			otherContentId,
			contentId,
		]);

		expect(updated.contentId).toBe(otherContentId);
		expect(updated.documents).toEqual([
			{ contentId: otherContentId },
			{ contentId },
		]);
		expect(updated.messages).toHaveLength(2);
	});

	it("streams deterministic tokens, conversation, and done", async () => {
		const repository = new MockConversationRepository();
		const tokens: Array<{ index: number; text: string }> = [];
		const eventOrder: string[] = [];

		await repository.streamQuestion(
			contentId,
			conversationId,
			"Why did Rome collapse?",
			{
				onToken: (token) => {
					eventOrder.push("token");
					tokens.push(token);
				},
				onConversation: (conversation) => {
					eventOrder.push("conversation");
					expect(conversation.id).toBe(conversationId);
					expect(conversation.messages).toHaveLength(2);
				},
				onDone: () => {
					eventOrder.push("done");
				},
				onError: () => {
					throw new Error("stream should not error");
				},
			},
		);

		expect(tokens.length).toBeGreaterThan(0);
		expect(eventOrder.at(-2)).toBe("conversation");
		expect(eventOrder.at(-1)).toBe("done");
	});

	it("preserves selected documents while streaming", async () => {
		const repository = new MockConversationRepository();
		const otherContentId = "550e8400-e29b-41d4-a716-446655440099";

		await repository.askQuestion(contentId, conversationId, "First question");
		await repository.updateDocuments(conversationId, [
			otherContentId,
			contentId,
		]);

		await repository.streamQuestion(
			contentId,
			conversationId,
			"Second question",
			{
				onToken: vi.fn(),
				onConversation: (conversation) => {
					expect(conversation.documents).toEqual([
						{ contentId: otherContentId },
						{ contentId },
					]);
				},
				onDone: vi.fn(),
				onError: () => {
					throw new Error("stream should not error");
				},
			},
		);
	});
});
