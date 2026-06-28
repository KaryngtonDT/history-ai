import { afterEach, describe, expect, it, vi } from "vitest";

describe("createRelationRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockRelationRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createRelationRepository } = await import(
			"./RelationRepositoryFactory"
		);
		const { MockRelationRepository } = await import("./MockRelationRepository");

		const repository = createRelationRepository();

		expect(repository).toBeInstanceOf(MockRelationRepository);
	}, 30_000);

	it("returns HttpRelationRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createRelationRepository } = await import(
			"./RelationRepositoryFactory"
		);
		const { HttpRelationRepository } = await import("./HttpRelationRepository");

		const repository = createRelationRepository();

		expect(repository).toBeInstanceOf(HttpRelationRepository);
	}, 15_000);
});
