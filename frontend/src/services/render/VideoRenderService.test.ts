import { describe, expect, it, vi } from "vitest";
import type { VideoRenderRepository } from "./VideoRenderRepository";
import { VideoRenderService } from "./VideoRenderService";

function createRepositoryMock(
	overrides: Partial<VideoRenderRepository> = {},
): VideoRenderRepository {
	return {
		listRenders: vi.fn().mockResolvedValue([]),
		getRender: vi.fn().mockResolvedValue(null),
		generateRender: vi.fn().mockResolvedValue(undefined),
		...overrides,
	};
}

describe("VideoRenderService", () => {
	it("rejects invalid video id on generate", async () => {
		const service = new VideoRenderService(createRepositoryMock());

		await expect(
			service.generateRender("invalid", {
				targetLanguages: ["french"],
			}),
		).rejects.toThrow("Invalid video id");
	});

	it("delegates generate to repository", async () => {
		const generateRender = vi.fn().mockResolvedValue(undefined);
		const service = new VideoRenderService(
			createRepositoryMock({ generateRender }),
		);

		await service.generateRender("550e8400-e29b-41d4-a716-446655440099", {
			targetLanguages: ["french"],
			format: "mp4",
			quality: "standard",
		});

		expect(generateRender).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
			{
				targetLanguages: ["french"],
				format: "mp4",
				quality: "standard",
			},
		);
	});
});
