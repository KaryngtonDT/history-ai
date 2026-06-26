import { afterEach, describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ApiError } from "@/shared/errors";
import { HttpProcessingRepository } from "./HttpProcessingRepository";

describe("HttpProcessingRepository", () => {
	afterEach(() => {
		vi.restoreAllMocks();
	});

	it("loads processing job from GET /api/processing-jobs/{id}", async () => {
		const get = vi.fn().mockResolvedValue({
			id: "job-1",
			contentId: "content-1",
			type: "summary",
			status: "pending",
			progress: 0,
			startedAt: null,
			completedAt: null,
			failedAt: null,
		});

		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpProcessingRepository(httpClient);
		const data = await repository.getProcessing("job-1");

		expect(get).toHaveBeenCalledWith("/api/processing-jobs/job-1");
		expect(data?.id).toBe("job-1");
		expect(data?.status).toBe("pending");
		expect(data?.progress).toBe(0);
		expect(data?.title).toBe("Summary processing");
	});

	it("returns null when job is not found", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 404));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;

		const repository = new HttpProcessingRepository(httpClient);

		await expect(repository.getProcessing("missing")).resolves.toBeNull();
	});

	it("propagates non-404 HTTP errors", async () => {
		const get = vi.fn().mockRejectedValue(new ApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;

		const repository = new HttpProcessingRepository(httpClient);

		await expect(repository.getProcessing("job-1")).rejects.toBeInstanceOf(
			ApiError,
		);
	});

	it("creates processing job via POST /api/contents/{id}/processing-jobs", async () => {
		const post = vi.fn().mockResolvedValue({
			id: "job-1",
			status: "pending",
			progress: 0,
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;

		const repository = new HttpProcessingRepository(httpClient);
		const result = await repository.createProcessingJob("content-1", "summary");

		expect(post).toHaveBeenCalledWith(
			"/api/contents/content-1/processing-jobs",
			{ type: "summary" },
		);
		expect(result).toEqual({
			id: "job-1",
			status: "pending",
			progress: 0,
		});
	});
});
