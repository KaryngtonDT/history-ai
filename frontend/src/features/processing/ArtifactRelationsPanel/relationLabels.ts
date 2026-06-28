import type { ArtifactType } from "@/services/artifact/types";
import type { ArtifactRelationType } from "@/services/relation/types";

export const ARTIFACT_TYPE_LABELS: Record<ArtifactType, string> = {
	transcript: "Transcript",
	summary: "Summary",
	quiz: "Quiz",
	flashcards: "Flashcards",
	timeline: "Timeline",
	podcast: "Podcast",
};

export const RELATION_TYPE_LABELS: Record<ArtifactRelationType, string> = {
	related: "Related",
	derived_from: "Derived from",
	references: "References",
	next: "Next",
	previous: "Previous",
};

export function getArtifactAnchor(type: ArtifactType): string {
	return `#artifact-${type}`;
}
