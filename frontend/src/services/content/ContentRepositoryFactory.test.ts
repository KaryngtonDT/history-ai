import { afterEach, describe, expect, it, vi } from "vitest";

describe("createContentRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockContentRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createContentRepository } = await import(
			"./ContentRepositoryFactory"
		);
		const { MockContentRepository } = await import("./MockContentRepository");

		const repository = createContentRepository();

		expect(repository).toBeInstanceOf(MockContentRepository);
	});

	it("returns HttpContentRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createContentRepository } = await import(
			"./ContentRepositoryFactory"
		);
		const { HttpContentRepository } = await import("./HttpContentRepository");

		const repository = createContentRepository();

		expect(repository).toBeInstanceOf(HttpContentRepository);
	});
});
