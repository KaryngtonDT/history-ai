import type { ArtifactType } from "@/services/artifact/types";
import type { GraphEdgeType } from "@/services/graph/types";

export const ARTIFACT_TYPE_LABELS: Record<ArtifactType, string> = {
	transcript: "Transcript",
	summary: "Summary",
	quiz: "Quiz",
	flashcards: "Flashcards",
	timeline: "Timeline",
	podcast: "Podcast",
};

export const EDGE_TYPE_LABELS: Record<GraphEdgeType, string> = {
	related: "Related",
	derived_from: "Derived from",
	references: "References",
	next: "Next",
	previous: "Previous",
};

export function getArtifactAnchor(type: ArtifactType): string {
	return `#artifact-${type}`;
}
