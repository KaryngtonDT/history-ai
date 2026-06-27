import { describe, expect, it, vi } from "vitest";
import type { SearchRepository } from "./SearchRepository";
import { SearchService } from "./SearchService";
import type { SearchLibraryItem } from "./types";

const summaryItem: SearchLibraryItem = {
	id: "library-item-1",
	contentId: "content-1",
	artifactId: "artifact-1",
	type: "summary",
	title: "Roman Empire Summary",
	createdAt: "2026-06-26T12:00:00+00:00",
};

function createRepositoryMock(
	overrides: Partial<SearchRepository> = {},
): SearchRepository {
	return {
		searchLibrary: vi.fn().mockResolvedValue([]),
		...overrides,
	};
}

describe("SearchService", () => {
	it("searchLibrary returns repository results", async () => {
		const searchLibrary = vi.fn().mockResolvedValue([summaryItem]);
		const service = new SearchService(createRepositoryMock({ searchLibrary }));

		const items = await service.searchLibrary("Roman");

		expect(searchLibrary).toHaveBeenCalledWith("Roman");
		expect(items).toEqual([summaryItem]);
	});

	it("returns empty array for empty query without calling repository", async () => {
		const searchLibrary = vi.fn();
		const service = new SearchService(createRepositoryMock({ searchLibrary }));

		const items = await service.searchLibrary("");

		expect(searchLibrary).not.toHaveBeenCalled();
		expect(items).toEqual([]);
	});

	it("returns empty array for whitespace-only query without calling repository", async () => {
		const searchLibrary = vi.fn();
		const service = new SearchService(createRepositoryMock({ searchLibrary }));

		const items = await service.searchLibrary("   ");

		expect(searchLibrary).not.toHaveBeenCalled();
		expect(items).toEqual([]);
	});

	it("trims query before delegating to repository", async () => {
		const searchLibrary = vi.fn().mockResolvedValue([summaryItem]);
		const service = new SearchService(createRepositoryMock({ searchLibrary }));

		await service.searchLibrary("  Roman  ");

		expect(searchLibrary).toHaveBeenCalledWith("Roman");
	});
});
