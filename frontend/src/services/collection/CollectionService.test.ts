import { describe, expect, it, vi } from "vitest";
import type { CollectionRepository } from "./CollectionRepository";
import { CollectionService } from "./CollectionService";
import type {
	Collection,
	CollectionItemAssignment,
	CreateCollectionInput,
} from "./types";

const collection: Collection = {
	id: "collection-1",
	name: "Ancient Rome",
	description: "Resources about Roman history",
	createdAt: "2026-06-27T12:00:00+00:00",
};

const assignment: CollectionItemAssignment = {
	id: "collection-item-1",
	collectionId: "collection-1",
	libraryItemId: "library-item-1",
	createdAt: "2026-06-27T12:30:00+00:00",
};

const createInput: CreateCollectionInput = {
	name: "Ancient Rome",
	description: "Resources about Roman history",
};

function createRepositoryMock(
	overrides: Partial<CollectionRepository> = {},
): CollectionRepository {
	return {
		listCollections: vi.fn().mockResolvedValue([]),
		createCollection: vi.fn().mockResolvedValue(collection),
		assignLibraryItem: vi.fn().mockResolvedValue(assignment),
		...overrides,
	};
}

describe("CollectionService", () => {
	it("listCollections returns collections", async () => {
		const listCollections = vi.fn().mockResolvedValue([collection]);
		const service = new CollectionService(
			createRepositoryMock({ listCollections }),
		);

		const collections = await service.listCollections();

		expect(listCollections).toHaveBeenCalledTimes(1);
		expect(collections).toEqual([collection]);
	});

	it("createCollection maps response", async () => {
		const createCollection = vi.fn().mockResolvedValue(collection);
		const service = new CollectionService(
			createRepositoryMock({ createCollection }),
		);

		const result = await service.createCollection(createInput);

		expect(createCollection).toHaveBeenCalledWith(createInput);
		expect(result).toEqual(collection);
	});

	it("assignLibraryItem maps response", async () => {
		const assignLibraryItem = vi.fn().mockResolvedValue(assignment);
		const service = new CollectionService(
			createRepositoryMock({ assignLibraryItem }),
		);

		const result = await service.assignLibraryItem(
			"collection-1",
			"library-item-1",
		);

		expect(assignLibraryItem).toHaveBeenCalledWith(
			"collection-1",
			"library-item-1",
		);
		expect(result).toEqual(assignment);
	});
});
