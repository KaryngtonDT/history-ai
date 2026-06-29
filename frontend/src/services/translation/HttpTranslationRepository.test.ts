import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpTranslationRepository } from "./HttpTranslationRepository";

describe("HttpTranslationRepository", () => {
	it("loads translations list", async () => {
		const get = vi.fn().mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			translations: [
				{
					videoId: "550e8400-e29b-41d4-a716-446655440099",
					translationId: "550e8400-e29b-41d4-a716-446655440020",
					sourceLanguage: "english",
					targetLanguage: "french",
					provider: "qwen",
					text: "Bonjour",
					segmentCount: 1,
				},
			],
		});
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTranslationRepository(httpClient);

		const translations = await repository.listTranslations(
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(get).toHaveBeenCalledWith(
			"/api/videos/550e8400-e29b-41d4-a716-446655440099/translations",
		);
		expect(translations[0]?.targetLanguage).toBe("french");
	});

	it("returns null when translation is missing", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 400));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpTranslationRepository(httpClient);

		const translation = await repository.getTranslation(
			"550e8400-e29b-41d4-a716-446655440099",
			"french",
		);

		expect(translation).toBeNull();
	});
});
