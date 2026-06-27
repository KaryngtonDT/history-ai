import { describe, expect, it, vi } from "vitest";
import type { HttpClient } from "@/services/http/HttpClient";
import { HttpCollectionRepository } from "./HttpCollectionRepository";

describe("HttpCollectionRepository", () => {
	it("uses GET /api/collections for listCollections", async () => {
		const get = vi.fn().mockResolvedValue([
			{
				id: "collection-1",
				name: "Ancient Rome",
				description: "Resources about Roman history",
				createdAt: "2026-06-27T12:00:00+00:00",
			},
		]);
		const httpClient = { get, post: vi.fn() } as unknown as HttpClient;
		const repository = new HttpCollectionRepository(httpClient);

		const collections = await repository.listCollections();

		expect(get).toHaveBeenCalledWith("/api/collections");
		expect(collections).toEqual([
			{
				id: "collection-1",
				name: "Ancient Rome",
				description: "Resources about Roman history",
				createdAt: "2026-06-27T12:00:00+00:00",
			},
		]);
	});

	it("uses POST /api/collections for createCollection", async () => {
		const post = vi.fn().mockResolvedValue({
			id: "collection-2",
			name: "Philosophy",
			description: "Philosophy resources",
			createdAt: "2026-06-27T11:00:00+00:00",
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpCollectionRepository(httpClient);

		const collection = await repository.createCollection({
			name: "Philosophy",
			description: "Philosophy resources",
		});

		expect(post).toHaveBeenCalledWith("/api/collections", {
			name: "Philosophy",
			description: "Philosophy resources",
		});
		expect(collection).toEqual({
			id: "collection-2",
			name: "Philosophy",
			description: "Philosophy resources",
			createdAt: "2026-06-27T11:00:00+00:00",
		});
	});

	it("uses POST /api/collections/{collectionId}/items for assignLibraryItem", async () => {
		const post = vi.fn().mockResolvedValue({
			id: "collection-item-2",
			collectionId: "collection-1",
			libraryItemId: "library-item-2",
			createdAt: "2026-06-27T12:45:00+00:00",
		});
		const httpClient = { get: vi.fn(), post } as unknown as HttpClient;
		const repository = new HttpCollectionRepository(httpClient);

		const result = await repository.assignLibraryItem(
			"collection-1",
			"library-item-2",
		);

		expect(post).toHaveBeenCalledWith("/api/collections/collection-1/items", {
			libraryItemId: "library-item-2",
		});
		expect(result).toEqual({
			id: "collection-item-2",
			collectionId: "collection-1",
			libraryItemId: "library-item-2",
			createdAt: "2026-06-27T12:45:00+00:00",
		});
	});
});
