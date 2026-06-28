import { afterEach, describe, expect, it, vi } from "vitest";

describe("createChatRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockChatRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createChatRepository } = await import("./ChatRepositoryFactory");
		const { MockChatRepository } = await import("./MockChatRepository");

		const repository = createChatRepository();

		expect(repository).toBeInstanceOf(MockChatRepository);
	}, 30_000);

	it("returns HttpChatRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createChatRepository } = await import("./ChatRepositoryFactory");
		const { HttpChatRepository } = await import("./HttpChatRepository");

		const repository = createChatRepository();

		expect(repository).toBeInstanceOf(HttpChatRepository);
	}, 15_000);
});
