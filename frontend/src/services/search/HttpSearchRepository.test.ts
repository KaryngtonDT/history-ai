import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { HttpSearchRepository } from "./HttpSearchRepository";

describe("HttpSearchRepository", () => {
	it("uses GET /api/search/library with encoded query parameter", async () => {
		const get = vi.fn().mockResolvedValue([
			{
				id: "library-item-1",
				contentId: "content-1",
				artifactId: "artifact-1",
				type: "summary",
				title: "Roman Empire Summary",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSearchRepository(httpClient);

		const items = await repository.searchLibrary("roman empire");

		expect(get).toHaveBeenCalledWith("/api/search/library?q=roman%20empire");
		expect(items).toEqual([
			{
				id: "library-item-1",
				contentId: "content-1",
				artifactId: "artifact-1",
				type: "summary",
				title: "Roman Empire Summary",
				createdAt: "2026-06-26T12:00:00+00:00",
			},
		]);
	});

	it("maps API DTO to frontend search item type", async () => {
		const get = vi.fn().mockResolvedValue([
			{
				id: "library-item-2",
				contentId: "content-2",
				artifactId: "artifact-2",
				type: "quiz",
				title: "Ancient Greece Quiz",
				createdAt: "2026-06-26T11:00:00+00:00",
			},
		]);
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpSearchRepository(httpClient);

		const items = await repository.searchLibrary("greece");

		expect(items[0]?.type).toBe("quiz");
		expect(items[0]?.title).toBe("Ancient Greece Quiz");
	});
});
