import { ProcessingQuiz } from "@/features/processing/ProcessingQuiz";
import type { ArtifactRendererProps } from "./ArtifactRenderer";

export function QuizArtifactRenderer({ artifact }: ArtifactRendererProps) {
	return <ProcessingQuiz artifact={artifact} />;
}
