import type { ArtifactType } from "@/services/artifact/types";
import type { ArtifactRenderer } from "./ArtifactRenderer";
import { FlashcardsArtifactRenderer } from "./FlashcardsArtifactRenderer";
import { QuizArtifactRenderer } from "./QuizArtifactRenderer";
import { SummaryArtifactRenderer } from "./SummaryArtifactRenderer";
import { TimelineArtifactRenderer } from "./TimelineArtifactRenderer";
import { TranscriptArtifactRenderer } from "./TranscriptArtifactRenderer";
import { UnsupportedArtifactRenderer } from "./UnsupportedArtifactRenderer";

export const ARTIFACT_DISPLAY_ORDER: readonly ArtifactType[] = [
	"summary",
	"transcript",
	"quiz",
	"flashcards",
	"timeline",
] as const;

const registry = new Map<string, ArtifactRenderer>([
	["summary", SummaryArtifactRenderer],
	["transcript", TranscriptArtifactRenderer],
	["quiz", QuizArtifactRenderer],
	["flashcards", FlashcardsArtifactRenderer],
	["timeline", TimelineArtifactRenderer],
]);

export function getArtifactRenderer(type: string): ArtifactRenderer {
	return registry.get(type) ?? UnsupportedArtifactRenderer;
}

export function isKnownArtifactType(type: string): boolean {
	return registry.has(type);
}
