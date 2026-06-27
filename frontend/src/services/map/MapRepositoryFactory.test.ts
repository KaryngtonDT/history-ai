import { afterEach, describe, expect, it, vi } from "vitest";

describe("createMapRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockMapRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createMapRepository } = await import("./MapRepositoryFactory");
		const { MockMapRepository } = await import("./MockMapRepository");

		const repository = createMapRepository();

		expect(repository).toBeInstanceOf(MockMapRepository);
	}, 30_000);

	it("returns HttpMapRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createMapRepository } = await import("./MapRepositoryFactory");
		const { HttpMapRepository } = await import("./HttpMapRepository");

		const repository = createMapRepository();

		expect(repository).toBeInstanceOf(HttpMapRepository);
	}, 15_000);
});
