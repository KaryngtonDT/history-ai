import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError, ValidationError } from "@/shared/errors";
import { HttpVideoRepository } from "./HttpVideoRepository";

describe("HttpVideoRepository", () => {
	it("uploads video via POST /api/videos multipart", async () => {
		const postFormData = vi.fn().mockResolvedValue({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "queued",
		});
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
			postFormData,
		} as unknown as HttpClient;
		const repository = new HttpVideoRepository(httpClient);
		const file = new File(["video"], "clip.mp4", { type: "video/mp4" });
		const onProgress = vi.fn();

		const result = await repository.uploadVideo(file, { onProgress });

		expect(postFormData).toHaveBeenCalledWith(
			"/api/videos",
			expect.any(FormData),
			{ onProgress },
		);
		expect(result).toEqual({
			videoId: "550e8400-e29b-41d4-a716-446655440099",
			status: "queued",
		});
	});

	it("throws ValidationError when API responds with 400", async () => {
		const postFormData = vi
			.fn()
			.mockRejectedValue(new ApiError("POST failed", 400));
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
			postFormData,
		} as unknown as HttpClient;
		const repository = new HttpVideoRepository(httpClient);
		const file = new File(["video"], "clip.mp4", { type: "video/mp4" });

		await expect(repository.uploadVideo(file)).rejects.toBeInstanceOf(
			ValidationError,
		);
	});

	it("propagates non-400 HTTP errors", async () => {
		const postFormData = vi
			.fn()
			.mockRejectedValue(new ApiError("POST failed", 500));
		const httpClient = {
			get: vi.fn(),
			post: vi.fn(),
			postFormData,
		} as unknown as HttpClient;
		const repository = new HttpVideoRepository(httpClient);
		const file = new File(["video"], "clip.mp4", { type: "video/mp4" });

		await expect(repository.uploadVideo(file)).rejects.toBeInstanceOf(ApiError);
	});
});
