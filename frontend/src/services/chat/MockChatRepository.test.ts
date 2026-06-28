import { describe, expect, it } from "vitest";
import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import { MockChatRepository } from "./MockChatRepository";
import { buildMockAnswerWithCitationMarkers } from "./types";

describe("MockChatRepository", () => {
	it("returns deterministic mock answer with sources and citations for mock content", async () => {
		const repository = new MockChatRepository();

		const result = await repository.askQuestion(
			ROMAN_EMPIRE_CONTENT_ID,
			"Roman",
		);

		expect(result.answer).toBe(
			buildMockAnswerWithCitationMarkers(result.sources.length),
		);
		expect(result.sources.length).toBeGreaterThan(0);
		expect(result.sources[0]).toMatchObject({
			artifactId: expect.any(String),
			chunkId: expect.any(String),
			score: 0.92,
		});
		expect(result.sources[0]?.text.toLowerCase()).toContain("roman");
		expect(result.citations).toHaveLength(result.sources.length);
		expect(result.citations[0]).toEqual({
			number: 1,
			artifactId: result.sources[0]?.artifactId,
			chunkId: result.sources[0]?.chunkId,
			score: result.sources[0]?.score,
		});
	});

	it("returns mock answer with empty sources and citations when mock content has no artifacts", async () => {
		const repository = new MockChatRepository();

		const result = await repository.askQuestion(
			"missing-content",
			"Why did Rome fall?",
		);

		expect(result).toEqual({
			answer: "Mock answer based on retrieved context.",
			sources: [],
			citations: [],
		});
	});

	it("returns mock answer with empty sources and citations when query does not match mock content", async () => {
		const repository = new MockChatRepository();

		const result = await repository.askQuestion(
			ROMAN_EMPIRE_CONTENT_ID,
			"byzantine",
		);

		expect(result).toEqual({
			answer: "Mock answer based on retrieved context.",
			sources: [],
			citations: [],
		});
	});
});
