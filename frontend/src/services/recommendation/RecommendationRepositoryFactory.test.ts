import { afterEach, describe, expect, it, vi } from "vitest";

describe("createRecommendationRepository", () => {
	afterEach(() => {
		vi.unstubAllEnvs();
		vi.resetModules();
	});

	it("returns MockRecommendationRepository when VITE_USE_MOCK=true", async () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const { createRecommendationRepository } = await import(
			"./RecommendationRepositoryFactory"
		);
		const { MockRecommendationRepository } = await import(
			"./MockRecommendationRepository"
		);

		const repository = createRecommendationRepository();

		expect(repository).toBeInstanceOf(MockRecommendationRepository);
	}, 30_000);

	it("returns HttpRecommendationRepository when VITE_USE_MOCK=false", async () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const { createRecommendationRepository } = await import(
			"./RecommendationRepositoryFactory"
		);
		const { HttpRecommendationRepository } = await import(
			"./HttpRecommendationRepository"
		);

		const repository = createRecommendationRepository();

		expect(repository).toBeInstanceOf(HttpRecommendationRepository);
	}, 15_000);
});
