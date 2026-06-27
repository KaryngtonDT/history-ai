import { describe, expect, it } from "vitest";
import { collectionMock } from "@/mock/collection";
import {
	CollectionAssignmentConflictError,
	MockCollectionRepository,
} from "./MockCollectionRepository";

describe("MockCollectionRepository", () => {
	it("returns mock collections", async () => {
		const repository = new MockCollectionRepository();
		const initialCount = collectionMock.collections.length;

		const collections = await repository.listCollections();

		expect(collections.length).toBeGreaterThanOrEqual(initialCount);
		expect(collections[0]?.name).toBe("Ancient Rome");
	});

	it("creates mock collections", async () => {
		const repository = new MockCollectionRepository();

		const collection = await repository.createCollection({
			name: "Languages",
			description: "Language learning resources",
		});

		expect(collection.name).toBe("Languages");
		expect(collectionMock.collections[0]?.name).toBe("Languages");
		collectionMock.collections.shift();
	});

	it("assigns library items to collections", async () => {
		const repository = new MockCollectionRepository();

		const assignment = await repository.assignLibraryItem(
			"collection-2",
			"library-item-99",
		);

		expect(assignment.collectionId).toBe("collection-2");
		expect(assignment.libraryItemId).toBe("library-item-99");
		expect(collectionMock.assignments[0]?.libraryItemId).toBe(
			"library-item-99",
		);
		collectionMock.assignments.shift();
	});

	it("rejects duplicate assignments", async () => {
		const repository = new MockCollectionRepository();

		await expect(
			repository.assignLibraryItem("collection-1", "library-item-1"),
		).rejects.toBeInstanceOf(CollectionAssignmentConflictError);
	});
});
