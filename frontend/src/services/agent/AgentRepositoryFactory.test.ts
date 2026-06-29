import { afterEach, describe, expect, it, vi } from "vitest";

describe("createAgentRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockAgentRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createAgentRepository } = await import("./AgentRepositoryFactory");
		const { MockAgentRepository } = await import("./MockAgentRepository");

		const repository = createAgentRepository();

		expect(repository).toBeInstanceOf(MockAgentRepository);
	}, 30_000);

	it("returns HttpAgentRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createAgentRepository } = await import("./AgentRepositoryFactory");
		const { HttpAgentRepository } = await import("./HttpAgentRepository");

		const repository = createAgentRepository();

		expect(repository).toBeInstanceOf(HttpAgentRepository);
	}, 15_000);
});
