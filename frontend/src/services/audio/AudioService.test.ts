import { describe, expect, it, vi } from "vitest";
import type { AudioRepository } from "./AudioRepository";
import { AudioService } from "./AudioService";

function createRepositoryMock(
	overrides: Partial<AudioRepository> = {},
): AudioRepository {
	return {
		listAudio: vi.fn().mockResolvedValue([]),
		getAudio: vi.fn().mockResolvedValue(null),
		generateAudio: vi.fn().mockResolvedValue(undefined),
		...overrides,
	};
}

describe("AudioService", () => {
	it("rejects invalid video id", async () => {
		const service = new AudioService(createRepositoryMock());

		await expect(
			service.generateAudio("invalid", {
				targetLanguages: ["french"],
				provider: "f5_tts",
				voiceId: "female_01",
			}),
		).rejects.toThrow("Invalid video id");
	});

	it("delegates generation to repository", async () => {
		const generateAudio = vi.fn().mockResolvedValue(undefined);
		const service = new AudioService(createRepositoryMock({ generateAudio }));

		await service.generateAudio("550e8400-e29b-41d4-a716-446655440099", {
			targetLanguages: ["french"],
			provider: "f5_tts",
			voiceId: "female_01",
		});

		expect(generateAudio).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
			{
				targetLanguages: ["french"],
				provider: "f5_tts",
				voiceId: "female_01",
			},
		);
	});
});
