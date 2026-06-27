import type { ArtifactType } from "@/services/artifact/types";

const LIBRARY_ITEM_TITLES: Record<ArtifactType, string> = {
	summary: "Summary",
	transcript: "Transcript",
	quiz: "Quiz",
	flashcards: "Flashcards",
	podcast: "Podcast",
	timeline: "Timeline",
};

export function deriveLibraryItemTitle(type: ArtifactType): string {
	return LIBRARY_ITEM_TITLES[type];
}
