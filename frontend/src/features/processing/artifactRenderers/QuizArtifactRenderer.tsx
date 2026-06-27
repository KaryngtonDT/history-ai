import { ProcessingQuiz } from "@/features/processing/ProcessingQuiz";
import type { ArtifactRendererProps } from "./ArtifactRenderer";

export function QuizArtifactRenderer({
	artifact,
	contentId,
	readOnly = false,
}: ArtifactRendererProps) {
	return (
		<ProcessingQuiz
			artifact={artifact}
			contentId={readOnly ? undefined : contentId}
		/>
	);
}
