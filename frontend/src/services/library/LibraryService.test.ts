import { describe, expect, it, vi } from "vitest";
import { libraryMock } from "@/mock/library";
import type { LibraryRepository } from "./LibraryRepository";
import { LibraryService } from "./LibraryService";
import type { LibraryItem } from "./types";

const summaryItem: LibraryItem = {
	id: "library-item-1",
	contentId: "content-1",
	artifactId: "artifact-1",
	type: "summary",
	title: "Roman Empire Summary",
	createdAt: "2026-06-26T12:00:00+00:00",
};

function createRepositoryMock(
	listItems: LibraryRepository["listItems"],
): LibraryRepository {
	return { listItems };
}

describe("LibraryService", () => {
	it("lists library items", async () => {
		const listItems = vi
			.fn<LibraryRepository["listItems"]>()
			.mockResolvedValue([summaryItem]);
		const service = new LibraryService(createRepositoryMock(listItems));

		const items = await service.listItems();

		expect(listItems).toHaveBeenCalledTimes(1);
		expect(items).toEqual([summaryItem]);
	});
});

describe("HttpLibraryRepository", () => {
	it("maps API DTO correctly", async () => {
		const { HttpLibraryRepository } = await import("./HttpLibraryRepository");
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
		const httpClient = { get, post: vi.fn() };
		const repository = new HttpLibraryRepository(
			httpClient as unknown as import("@/services/http/HttpClient").HttpClient,
		);

		const items = await repository.listItems();

		expect(get).toHaveBeenCalledWith("/api/library/items");
		expect(items).toEqual([summaryItem]);
	});
});

describe("MockLibraryRepository", () => {
	it("returns mock library items", async () => {
		const { MockLibraryRepository } = await import("./MockLibraryRepository");
		const repository = new MockLibraryRepository();

		const items = await repository.listItems();

		expect(items).toEqual(libraryMock.items);
	});
});
