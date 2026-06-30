import { describe, expect, it, vi } from "vitest";
import type { LipSyncRepository } from "./LipSyncRepository";
import { LipSyncService } from "./LipSyncService";

function createRepositoryMock(
	overrides: Partial<LipSyncRepository> = {},
): LipSyncRepository {
	return {
		listLipSyncs: vi.fn().mockResolvedValue([]),
		getLipSync: vi.fn().mockResolvedValue(null),
		generateLipSync: vi.fn().mockResolvedValue(undefined),
		...overrides,
	};
}

describe("LipSyncService", () => {
	it("rejects invalid video id", async () => {
		const service = new LipSyncService(createRepositoryMock());

		await expect(
			service.generateLipSync("invalid", {
				targetLanguages: ["french"],
				provider: "latentsync",
			}),
		).rejects.toThrow("Invalid video id");
	});

	it("delegates generation to repository", async () => {
		const generateLipSync = vi.fn().mockResolvedValue(undefined);
		const service = new LipSyncService(
			createRepositoryMock({ generateLipSync }),
		);

		await service.generateLipSync("550e8400-e29b-41d4-a716-446655440099", {
			targetLanguages: ["french"],
			provider: "latentsync",
		});

		expect(generateLipSync).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
			{
				targetLanguages: ["french"],
				provider: "latentsync",
			},
		);
	});
});
