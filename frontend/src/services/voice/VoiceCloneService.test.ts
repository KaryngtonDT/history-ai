import { describe, expect, it, vi } from "vitest";
import type { VoiceCloneRepository } from "./VoiceCloneRepository";
import { VoiceCloneService } from "./VoiceCloneService";

function createRepositoryMock(
	overrides: Partial<VoiceCloneRepository> = {},
): VoiceCloneRepository {
	return {
		listVoiceClones: vi.fn().mockResolvedValue([]),
		getVoiceClone: vi.fn().mockResolvedValue(null),
		generateVoiceClone: vi.fn().mockResolvedValue(undefined),
		...overrides,
	};
}

describe("VoiceCloneService", () => {
	it("rejects invalid video id", async () => {
		const service = new VoiceCloneService(createRepositoryMock());

		await expect(
			service.generateVoiceClone("invalid", {
				targetLanguages: ["french"],
				provider: "openvoice",
				voiceMode: "clone",
			}),
		).rejects.toThrow("Invalid video id");
	});

	it("delegates generation to repository", async () => {
		const generateVoiceClone = vi.fn().mockResolvedValue(undefined);
		const service = new VoiceCloneService(
			createRepositoryMock({ generateVoiceClone }),
		);

		await service.generateVoiceClone("550e8400-e29b-41d4-a716-446655440099", {
			targetLanguages: ["french"],
			provider: "openvoice",
			voiceMode: "clone",
		});

		expect(generateVoiceClone).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
			{
				targetLanguages: ["french"],
				provider: "openvoice",
				voiceMode: "clone",
			},
		);
	});
});
