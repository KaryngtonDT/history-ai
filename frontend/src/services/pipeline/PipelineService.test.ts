import { describe, expect, it, vi } from "vitest";
import type { PipelineRepository } from "./PipelineRepository";
import { PipelineService } from "./PipelineService";

function createRepositoryMock(
	overrides: Partial<PipelineRepository> = {},
): PipelineRepository {
	return {
		loadConfiguration: vi.fn().mockResolvedValue({
			id: "550e8400-e29b-41d4-a716-446655440010",
			version: 1,
			createdAt: "",
			updatedAt: "",
			stages: [],
		}),
		saveConfiguration: vi.fn().mockResolvedValue({
			id: "550e8400-e29b-41d4-a716-446655440010",
			version: 2,
			createdAt: "",
			updatedAt: "",
			stages: [{ stage: "translation", providerId: "ollama" }],
		}),
		resetConfiguration: vi.fn().mockResolvedValue({
			id: "550e8400-e29b-41d4-a716-446655440010",
			version: 1,
			createdAt: "",
			updatedAt: "",
			stages: [],
		}),
		...overrides,
	};
}

describe("PipelineService", () => {
	it("delegates save to repository", async () => {
		const saveConfiguration = vi.fn().mockResolvedValue({
			id: "550e8400-e29b-41d4-a716-446655440010",
			version: 2,
			createdAt: "",
			updatedAt: "",
			stages: [{ stage: "translation", providerId: "ollama" }],
		});
		const service = new PipelineService(
			createRepositoryMock({ saveConfiguration }),
		);

		await service.saveConfiguration([
			{ stage: "translation", providerId: "ollama" },
		]);

		expect(saveConfiguration).toHaveBeenCalled();
	});
});
