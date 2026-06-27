import { describe, expect, it, vi } from "vitest";
import { libraryMock } from "@/mock/library";
import type { LibraryRepository } from "./LibraryRepository";
import { LibraryService } from "./LibraryService";
import type { AddLibraryItemInput, LibraryItem } from "./types";

const summaryItem: LibraryItem = {
	id: "library-item-1",
	contentId: "content-1",
	artifactId: "artifact-1",
	type: "summary",
	title: "Roman Empire Summary",
	createdAt: "2026-06-26T12:00:00+00:00",
};

const addInput: AddLibraryItemInput = {
	contentId: "content-1",
	artifactId: "artifact-1",
	type: "summary",
	title: "Summary",
};

function createRepositoryMock(
	overrides: Partial<LibraryRepository> = {},
): LibraryRepository {
	return {
		listItems: vi.fn().mockResolvedValue([]),
		addItem: vi.fn().mockResolvedValue(summaryItem),
		...overrides,
	};
}

describe("LibraryService", () => {
	it("lists library items", async () => {
		const listItems = vi.fn().mockResolvedValue([summaryItem]);
		const service = new LibraryService(createRepositoryMock({ listItems }));

		const items = await service.listItems();

		expect(listItems).toHaveBeenCalledTimes(1);
		expect(items).toEqual([summaryItem]);
	});

	it("addItem calls repository", async () => {
		const addItem = vi.fn().mockResolvedValue(summaryItem);
		const service = new LibraryService(createRepositoryMock({ addItem }));

		const result = await service.addItem(addInput);

		expect(addItem).toHaveBeenCalledWith(addInput);
		expect(result).toEqual(summaryItem);
	});
});

describe("HttpLibraryRepository", () => {
	it("maps list API DTO correctly", async () => {
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

	it("maps addItem API response correctly", async () => {
		const { HttpLibraryRepository } = await import("./HttpLibraryRepository");
		const post = vi.fn().mockResolvedValue({
			id: "library-item-2",
			type: "summary",
			title: "Summary",
			createdAt: "2026-06-26T12:00:01+00:00",
		});
		const httpClient = { get: vi.fn(), post };
		const repository = new HttpLibraryRepository(
			httpClient as unknown as import("@/services/http/HttpClient").HttpClient,
		);

		const item = await repository.addItem(addInput);

		expect(post).toHaveBeenCalledWith("/api/library/items", addInput);
		expect(item).toEqual({
			id: "library-item-2",
			contentId: "content-1",
			artifactId: "artifact-1",
			type: "summary",
			title: "Summary",
			createdAt: "2026-06-26T12:00:01+00:00",
		});
	});
});

describe("MockLibraryRepository", () => {
	it("returns mock library items", async () => {
		const { MockLibraryRepository } = await import("./MockLibraryRepository");
		const repository = new MockLibraryRepository();
		const initialCount = libraryMock.items.length;

		const items = await repository.listItems();

		expect(items.length).toBeGreaterThanOrEqual(initialCount);
	});

	it("adds mock library items", async () => {
		const { MockLibraryRepository } = await import("./MockLibraryRepository");
		const repository = new MockLibraryRepository();

		const item = await repository.addItem({
			contentId: "content-99",
			artifactId: "artifact-99",
			type: "quiz",
			title: "Quiz",
		});

		expect(item.title).toBe("Quiz");
		expect(libraryMock.items[0]?.artifactId).toBe("artifact-99");
		libraryMock.items.shift();
	});
});
