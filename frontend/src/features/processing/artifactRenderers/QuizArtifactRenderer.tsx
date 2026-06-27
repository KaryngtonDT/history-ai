import { ProcessingQuiz } from "@/features/processing/ProcessingQuiz";
import type { ArtifactRendererProps } from "./ArtifactRenderer";

export function QuizArtifactRenderer({
	artifact,
	contentId,
}: ArtifactRendererProps) {
	return <ProcessingQuiz artifact={artifact} contentId={contentId} />;
}
