import type {
	Collection,
	CollectionItemAssignment,
} from "@/services/collection/types";

export const collectionMock: {
	collections: Collection[];
	assignments: CollectionItemAssignment[];
} = {
	collections: [
		{
			id: "collection-1",
			name: "Ancient Rome",
			description: "Resources about Roman history",
			createdAt: "2026-06-27T12:00:00+00:00",
		},
		{
			id: "collection-2",
			name: "Philosophy",
			description: "Philosophy resources",
			createdAt: "2026-06-27T11:00:00+00:00",
		},
	],
	assignments: [
		{
			id: "collection-item-1",
			collectionId: "collection-1",
			libraryItemId: "library-item-1",
			createdAt: "2026-06-27T12:30:00+00:00",
		},
	],
};
