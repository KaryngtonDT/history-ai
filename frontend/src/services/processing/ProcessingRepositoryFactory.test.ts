import { afterEach, describe, expect, it, vi } from "vitest";

describe("createProcessingRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockProcessingRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createProcessingRepository } = await import(
			"./ProcessingRepositoryFactory"
		);
		const { MockProcessingRepository } = await import(
			"./MockProcessingRepository"
		);

		const repository = createProcessingRepository();

		expect(repository).toBeInstanceOf(MockProcessingRepository);
	});

	it("returns HttpProcessingRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createProcessingRepository } = await import(
			"./ProcessingRepositoryFactory"
		);
		const { HttpProcessingRepository } = await import(
			"./HttpProcessingRepository"
		);

		const repository = createProcessingRepository();

		expect(repository).toBeInstanceOf(HttpProcessingRepository);
	});
});
