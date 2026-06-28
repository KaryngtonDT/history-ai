import { afterEach, describe, expect, it, vi } from "vitest";

describe("createGraphRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockGraphRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createGraphRepository } = await import("./GraphRepositoryFactory");
		const { MockGraphRepository } = await import("./MockGraphRepository");

		const repository = createGraphRepository();

		expect(repository).toBeInstanceOf(MockGraphRepository);
	}, 30_000);

	it("returns HttpGraphRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createGraphRepository } = await import("./GraphRepositoryFactory");
		const { HttpGraphRepository } = await import("./HttpGraphRepository");

		const repository = createGraphRepository();

		expect(repository).toBeInstanceOf(HttpGraphRepository);
	}, 15_000);
});
