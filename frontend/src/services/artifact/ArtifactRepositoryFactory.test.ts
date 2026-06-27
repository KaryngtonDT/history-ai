import { afterEach, describe, expect, it, vi } from "vitest";

describe("createArtifactRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockArtifactRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createArtifactRepository } = await import(
			"./ArtifactRepositoryFactory"
		);
		const { MockArtifactRepository } = await import("./MockArtifactRepository");

		const repository = createArtifactRepository();

		expect(repository).toBeInstanceOf(MockArtifactRepository);
	}, 30_000);

	it("returns HttpArtifactRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createArtifactRepository } = await import(
			"./ArtifactRepositoryFactory"
		);
		const { HttpArtifactRepository } = await import("./HttpArtifactRepository");

		const repository = createArtifactRepository();

		expect(repository).toBeInstanceOf(HttpArtifactRepository);
	}, 15_000);
});
