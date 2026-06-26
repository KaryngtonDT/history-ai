import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { ContentApiError } from "./ContentApiError";
import { HttpContentRepository } from "./HttpContentRepository";

describe("HttpContentRepository", () => {
	it("lists contents from GET /api/contents", async () => {
		const get = vi.fn().mockResolvedValue([
			{
				id: "1",
				title: "The Roman Empire",
				sourceType: "upload_pdf",
				status: "processing",
				createdAt: "2026-06-26T12:00:00+00:00",
				updatedAt: "2026-06-26T12:00:00+00:00",
			},
		]);

		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpContentRepository(httpClient);
		const contents = await repository.listContents();

		expect(get).toHaveBeenCalledWith("/api/contents");
		expect(contents[0]?.title).toBe("The Roman Empire");
		expect(contents[0]?.sourceType).toBe("pdf");
	});

	it("creates content via POST /api/contents", async () => {
		const post = vi.fn().mockResolvedValue({ id: "new-id" });
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;

		const repository = new HttpContentRepository(httpClient);
		const result = await repository.createContent({
			title: "French Revolution",
			sourceType: "pdf",
		});

		expect(post).toHaveBeenCalledWith("/api/contents", {
			title: "French Revolution",
			sourceType: "upload_pdf",
		});
		expect(result).toEqual({ id: "new-id" });
	});

	it("propagates HTTP errors from HttpClient", async () => {
		const get = vi
			.fn()
			.mockRejectedValue(new ContentApiError("GET failed", 500));
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;

		const repository = new HttpContentRepository(httpClient);

		await expect(repository.listContents()).rejects.toBeInstanceOf(
			ContentApiError,
		);
	});
});
