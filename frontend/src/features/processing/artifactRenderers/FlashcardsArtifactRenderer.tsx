import { ProcessingFlashcards } from "@/features/processing/ProcessingFlashcards";
import type { ArtifactRendererProps } from "./ArtifactRenderer";

export function FlashcardsArtifactRenderer({
	artifact,
}: ArtifactRendererProps) {
	return <ProcessingFlashcards artifact={artifact} />;
}
