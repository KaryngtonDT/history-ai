import { describe, expect, it } from "vitest";
import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import { MockSemanticSearchRepository } from "./MockSemanticSearchRepository";

describe("MockSemanticSearchRepository", () => {
	it("returns semantic results for mock content matching query", async () => {
		const repository = new MockSemanticSearchRepository();

		const results = await repository.searchSemanticChunks(
			ROMAN_EMPIRE_CONTENT_ID,
			"Roman",
		);

		expect(results.length).toBeGreaterThan(0);
		expect(results[0]).toMatchObject({
			artifactId: expect.any(String),
			chunkId: expect.any(String),
			position: 0,
			score: 0.92,
		});
		expect(results[0]?.text.toLowerCase()).toContain("roman");
	});

	it("returns empty array when mock content has no artifacts", async () => {
		const repository = new MockSemanticSearchRepository();

		const results = await repository.searchSemanticChunks(
			"missing-content",
			"rome",
		);

		expect(results).toEqual([]);
	});

	it("returns empty array when query does not match mock content", async () => {
		const repository = new MockSemanticSearchRepository();

		const results = await repository.searchSemanticChunks(
			ROMAN_EMPIRE_CONTENT_ID,
			"byzantine",
		);

		expect(results).toEqual([]);
	});
});
