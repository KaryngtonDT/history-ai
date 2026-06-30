import { describe, expect, it, vi } from "vitest";
import { MOCK_PREVIEW_QUALITY } from "./MockQualityRepository";
import type { QualityRepository } from "./QualityRepository";
import { QualityService } from "./QualityService";

function createRepositoryMock(
	overrides: Partial<QualityRepository> = {},
): QualityRepository {
	return {
		getPreviewQuality: vi.fn().mockResolvedValue(MOCK_PREVIEW_QUALITY),
		getByVideoId: vi.fn().mockResolvedValue(MOCK_PREVIEW_QUALITY),
		...overrides,
	};
}

describe("QualityService", () => {
	it("loads preview quality from repository", async () => {
		const getPreviewQuality = vi.fn().mockResolvedValue(MOCK_PREVIEW_QUALITY);
		const service = new QualityService(
			createRepositoryMock({ getPreviewQuality }),
		);

		const result = await service.loadPreviewQuality();

		expect(getPreviewQuality).toHaveBeenCalledOnce();
		expect(result).toEqual(MOCK_PREVIEW_QUALITY);
	});

	it("loads quality by video id", async () => {
		const getByVideoId = vi.fn().mockResolvedValue({
			...MOCK_PREVIEW_QUALITY,
			videoId: "550e8400-e29b-41d4-a716-446655440099",
		});
		const service = new QualityService(createRepositoryMock({ getByVideoId }));

		const result = await service.loadByVideoId(
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(getByVideoId).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
		);
		expect(result.videoId).toBe("550e8400-e29b-41d4-a716-446655440099");
	});

	it("formats category and recommendation labels", () => {
		const service = new QualityService(createRepositoryMock());

		expect(service.formatCategory("lip_sync")).toBe("Lip Sync");
		expect(service.formatRecommendation("ready")).toBe("Ready for publishing");
		expect(service.isReadyForPublishing("ready")).toBe(true);
	});
});
