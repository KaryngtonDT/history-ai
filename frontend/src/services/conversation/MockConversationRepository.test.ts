import { describe, expect, it } from "vitest";
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
});
