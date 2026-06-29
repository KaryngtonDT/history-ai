import { afterEach, describe, expect, it, vi } from "vitest";

describe("createConversationRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockConversationRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createConversationRepository } = await import(
			"./ConversationRepositoryFactory"
		);
		const { MockConversationRepository } = await import(
			"./MockConversationRepository"
		);

		const repository = createConversationRepository();

		expect(repository).toBeInstanceOf(MockConversationRepository);
	}, 30_000);

	it("returns HttpConversationRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createConversationRepository } = await import(
			"./ConversationRepositoryFactory"
		);
		const { HttpConversationRepository } = await import(
			"./HttpConversationRepository"
		);

		const repository = createConversationRepository();

		expect(repository).toBeInstanceOf(HttpConversationRepository);
	}, 15_000);
});
