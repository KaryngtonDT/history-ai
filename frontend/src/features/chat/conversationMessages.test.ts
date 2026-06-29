import { describe, expect, it } from "vitest";
import { mapConversationToChatMessageItems } from "./conversationMessages";

describe("mapConversationToChatMessageItems", () => {
	it("maps conversation messages and attaches answer metadata to the latest assistant message", () => {
		const items = mapConversationToChatMessageItems({
			conversation: {
				id: "550e8400-e29b-41d4-a716-446655440001",
				contentId: "550e8400-e29b-41d4-a716-446655440000",
				messages: [
					{ role: "user", text: "First question" },
					{ role: "assistant", text: "First answer" },
					{ role: "user", text: "Second question" },
					{
						role: "assistant",
						text: "Second answer with citation [1].",
					},
				],
			},
			answer: {
				answer: "Second answer with citation [1].",
				sources: [
					{
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						text: "## Ancient Rome",
						score: 0.87,
					},
				],
				citations: [
					{
						number: 1,
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
						score: 0.87,
					},
				],
			},
		});

		expect(items).toHaveLength(4);
		expect(items[1].sources).toBeUndefined();
		expect(items[3].sources).toHaveLength(1);
		expect(items[3].citations).toHaveLength(1);
	});
});
