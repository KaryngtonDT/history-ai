import { ProcessingFlashcards } from "@/features/processing/ProcessingFlashcards";
import type { ArtifactRendererProps } from "./ArtifactRenderer";

export function FlashcardsArtifactRenderer({
	artifact,
	contentId,
	readOnly = false,
}: ArtifactRendererProps) {
	return (
		<ProcessingFlashcards
			artifact={artifact}
			contentId={readOnly ? undefined : contentId}
		/>
	);
}
