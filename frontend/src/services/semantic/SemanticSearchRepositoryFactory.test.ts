import { afterEach, describe, expect, it, vi } from "vitest";

describe("createSemanticSearchRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockSemanticSearchRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createSemanticSearchRepository } = await import(
			"./SemanticSearchRepositoryFactory"
		);
		const { MockSemanticSearchRepository } = await import(
			"./MockSemanticSearchRepository"
		);

		const repository = createSemanticSearchRepository();

		expect(repository).toBeInstanceOf(MockSemanticSearchRepository);
	}, 30_000);

	it("returns HttpSemanticSearchRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createSemanticSearchRepository } = await import(
			"./SemanticSearchRepositoryFactory"
		);
		const { HttpSemanticSearchRepository } = await import(
			"./HttpSemanticSearchRepository"
		);

		const repository = createSemanticSearchRepository();

		expect(repository).toBeInstanceOf(HttpSemanticSearchRepository);
	}, 15_000);
});
