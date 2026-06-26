import { describe, expect, it, vi } from "vitest";
import { createContentRepository } from "./ContentRepositoryFactory";
import { HttpContentRepository } from "./HttpContentRepository";
import { MockContentRepository } from "./MockContentRepository";

describe("createContentRepository", () => {
	it("returns MockContentRepository when VITE_USE_MOCK=true", () => {
		vi.stubEnv("VITE_USE_MOCK", "true");

		const repository = createContentRepository();

		expect(repository).toBeInstanceOf(MockContentRepository);
	});

	it("returns HttpContentRepository when VITE_USE_MOCK=false", () => {
		vi.stubEnv("VITE_USE_MOCK", "false");

		const repository = createContentRepository();

		expect(repository).toBeInstanceOf(HttpContentRepository);
	});
});
