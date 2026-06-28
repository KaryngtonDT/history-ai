import { describe, expect, it, vi } from "vitest";
import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import { MockChatRepository } from "./MockChatRepository";
import {
	buildMockAnswerWithCitationMarkers,
	buildMockStreamTokens,
} from "./types";

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

	it("emits deterministic stream tokens and done callback", async () => {
		const repository = new MockChatRepository();
		const answer = await repository.askQuestion(
			ROMAN_EMPIRE_CONTENT_ID,
			"Roman",
		);
		const tokens: Array<{ index: number; text: string }> = [];
		const onDone = vi.fn();
		const onError = vi.fn();

		await repository.streamQuestion(ROMAN_EMPIRE_CONTENT_ID, "Roman", {
			onToken: (token) => {
				tokens.push(token);
			},
			onDone,
			onError,
		});

		expect(tokens.map((token) => token.text)).toEqual(
			buildMockStreamTokens(answer.sources.length),
		);
		expect(tokens.map((token) => token.index)).toEqual(
			Array.from({ length: tokens.length }, (_, index) => index),
		);
		expect(onDone).toHaveBeenCalledOnce();
		expect(onError).not.toHaveBeenCalled();
	});

	it("emits citation tokens for mock content with sources", async () => {
		const repository = new MockChatRepository();
		const answer = await repository.askQuestion(
			ROMAN_EMPIRE_CONTENT_ID,
			"Roman",
		);
		const tokens: string[] = [];

		await repository.streamQuestion(ROMAN_EMPIRE_CONTENT_ID, "Roman", {
			onToken: (token) => {
				tokens.push(token.text);
			},
			onDone: vi.fn(),
			onError: vi.fn(),
		});

		expect(tokens).toEqual(buildMockStreamTokens(answer.sources.length));
	});

	it("emits trailing period token when mock content has no sources", async () => {
		const repository = new MockChatRepository();
		const tokens: string[] = [];

		await repository.streamQuestion("missing-content", "Why did Rome fall?", {
			onToken: (token) => {
				tokens.push(token.text);
			},
			onDone: vi.fn(),
			onError: vi.fn(),
		});

		expect(tokens).toEqual(buildMockStreamTokens(0));
	});
});
