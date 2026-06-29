import { describe, expect, it, vi } from "vitest";
import type { TranslationRepository } from "./TranslationRepository";
import { TranslationService } from "./TranslationService";

function createRepositoryMock(
	overrides: Partial<TranslationRepository> = {},
): TranslationRepository {
	return {
		listTranslations: vi.fn().mockResolvedValue([]),
		getTranslation: vi.fn().mockResolvedValue(null),
		generateTranslations: vi.fn().mockResolvedValue(undefined),
		...overrides,
	};
}

describe("TranslationService", () => {
	it("rejects invalid video id", async () => {
		const service = new TranslationService(createRepositoryMock());

		await expect(
			service.generateTranslations("invalid", {
				targetLanguages: ["french"],
				provider: "qwen",
			}),
		).rejects.toThrow("Invalid video id");
	});

	it("delegates generation to repository", async () => {
		const generateTranslations = vi.fn().mockResolvedValue(undefined);
		const service = new TranslationService(
			createRepositoryMock({ generateTranslations }),
		);

		await service.generateTranslations("550e8400-e29b-41d4-a716-446655440099", {
			targetLanguages: ["french", "german"],
			provider: "qwen",
		});

		expect(generateTranslations).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
			{
				targetLanguages: ["french", "german"],
				provider: "qwen",
			},
		);
	});
});
