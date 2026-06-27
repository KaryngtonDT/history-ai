import { describe, expect, it } from "vitest";
import { MockSearchRepository } from "./MockSearchRepository";

describe("MockSearchRepository", () => {
	it("returns mock items matching the query", async () => {
		const repository = new MockSearchRepository();

		const items = await repository.searchLibrary("Roman");

		expect(items.length).toBeGreaterThan(0);
		expect(
			items.every((item) => item.title.toLowerCase().includes("roman")),
		).toBe(true);
	});

	it("returns empty array when no mock item matches", async () => {
		const repository = new MockSearchRepository();

		const items = await repository.searchLibrary("byzantine");

		expect(items).toEqual([]);
	});

	it("matches case-insensitively", async () => {
		const repository = new MockSearchRepository();

		const items = await repository.searchLibrary("ANCIENT");

		expect(
			items.some((item) => item.title.includes("Ancient Greece Quiz")),
		).toBe(true);
	});
});
