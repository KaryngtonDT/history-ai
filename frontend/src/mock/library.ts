import type { LibraryItem } from "@/services/library/types";

export const libraryMock: { items: LibraryItem[] } = {
	items: [
		{
			id: "library-item-1",
			contentId: "content-1",
			artifactId: "artifact-1",
			type: "summary",
			title: "The Roman Empire",
			createdAt: "2026-06-26T12:00:00+00:00",
		},
		{
			id: "library-item-2",
			contentId: "content-2",
			artifactId: "artifact-2",
			type: "quiz",
			title: "Ancient Greece Quiz",
			createdAt: "2026-06-26T11:00:00+00:00",
		},
		{
			id: "library-item-3",
			contentId: "content-3",
			artifactId: "artifact-3",
			type: "flashcards",
			title: "YouTube Lecture Flashcards",
			createdAt: "2026-06-26T10:00:00+00:00",
		},
	],
};
