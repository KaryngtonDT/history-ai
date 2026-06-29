import { afterEach, describe, expect, it, vi } from "vitest";

describe("createVideoRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockVideoRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createVideoRepository } = await import("./VideoRepositoryFactory");
		const { MockVideoRepository } = await import("./MockVideoRepository");

		const repository = createVideoRepository();

		expect(repository).toBeInstanceOf(MockVideoRepository);
	}, 30_000);

	it("returns HttpVideoRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createVideoRepository } = await import("./VideoRepositoryFactory");
		const { HttpVideoRepository } = await import("./HttpVideoRepository");

		const repository = createVideoRepository();

		expect(repository).toBeInstanceOf(HttpVideoRepository);
	}, 15_000);
});
