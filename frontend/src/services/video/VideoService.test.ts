import { describe, expect, it, vi } from "vitest";
import { ValidationError } from "@/shared/errors";
import type { VideoRepository } from "./VideoRepository";
import { VideoService } from "./VideoService";

function createRepositoryMock(
	overrides: Partial<VideoRepository> = {},
): VideoRepository {
	return {
		uploadVideo: vi.fn().mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "queued",
		}),
		getStatus: vi.fn().mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "completed",
			originalFilename: "lecture.mp4",
			language: "unknown",
			createdAt: new Date().toISOString(),
		}),
		processVideo: vi.fn().mockResolvedValue({ status: "queued" }),
		...overrides,
	};
}

describe("VideoService", () => {
	it("accepts supported video extensions", () => {
		const service = new VideoService(createRepositoryMock());

		expect(service.validateVideo(new File(["v"], "clip.mp4")).valid).toBe(true);
		expect(service.validateVideo(new File(["v"], "clip.mov")).valid).toBe(true);
		expect(service.validateVideo(new File(["v"], "clip.mkv")).valid).toBe(true);
	});

	it("rejects unsupported files", () => {
		const service = new VideoService(createRepositoryMock());
		const result = service.validateVideo(
			new File(["notes"], "notes.txt", { type: "text/plain" }),
		);

		expect(result.valid).toBe(false);
		if (!result.valid) {
			expect(result.error).toBe(
				"Only MP4, MOV, and MKV video files are supported.",
			);
		}
	});

	it("delegates valid uploads to repository with progress callback", async () => {
		const uploadVideo = vi.fn().mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "queued",
		});
		const service = new VideoService(createRepositoryMock({ uploadVideo }));
		const file = new File(["video"], "clip.mp4", { type: "video/mp4" });
		const onProgress = vi.fn();

		const result = await service.uploadVideo(file, { onProgress });

		expect(uploadVideo).toHaveBeenCalledWith(file, { onProgress });
		expect(result.status).toBe("queued");
	});

	it("throws ValidationError for invalid files before repository call", async () => {
		const uploadVideo = vi.fn();
		const service = new VideoService(createRepositoryMock({ uploadVideo }));
		const file = new File(["notes"], "notes.txt", { type: "text/plain" });

		await expect(
			service.uploadVideo(file, { onProgress: vi.fn() }),
		).rejects.toBeInstanceOf(ValidationError);
		expect(uploadVideo).not.toHaveBeenCalled();
	});
});
