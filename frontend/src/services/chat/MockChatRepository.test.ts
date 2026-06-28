import { describe, expect, it } from "vitest";
import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import { MockChatRepository } from "./MockChatRepository";
import { MOCK_CHAT_ANSWER } from "./types";

describe("MockChatRepository", () => {
	it("returns deterministic mock answer with sources for mock content", async () => {
		const repository = new MockChatRepository();

		const result = await repository.askQuestion(
			ROMAN_EMPIRE_CONTENT_ID,
			"Roman",
		);

		expect(result.answer).toBe(MOCK_CHAT_ANSWER);
		expect(result.sources.length).toBeGreaterThan(0);
		expect(result.sources[0]).toMatchObject({
			artifactId: expect.any(String),
			chunkId: expect.any(String),
			score: 0.92,
		});
		expect(result.sources[0]?.text.toLowerCase()).toContain("roman");
	});

	it("returns mock answer with empty sources when mock content has no artifacts", async () => {
		const repository = new MockChatRepository();

		const result = await repository.askQuestion(
			"missing-content",
			"Why did Rome fall?",
		);

		expect(result).toEqual({
			answer: MOCK_CHAT_ANSWER,
			sources: [],
		});
	});

	it("returns mock answer with empty sources when query does not match mock content", async () => {
		const repository = new MockChatRepository();

		const result = await repository.askQuestion(
			ROMAN_EMPIRE_CONTENT_ID,
			"byzantine",
		);

		expect(result).toEqual({
			answer: MOCK_CHAT_ANSWER,
			sources: [],
		});
	});
});
