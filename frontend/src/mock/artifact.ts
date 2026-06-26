import type { Artifact } from "@/services/artifact/types";
import { generateSummaryFromTranscript } from "./summaryGenerator";

export const MOCK_TRANSCRIPT =
	"The Roman Empire was vast. It lasted many centuries. Its legacy shaped Europe. Modern law still reflects Roman ideas. Archaeology continues to reveal new sites.";

export const MOCK_SUMMARY = generateSummaryFromTranscript(MOCK_TRANSCRIPT);

function buildMockArtifactsForContent(
	contentId: string,
	processingJobId: string,
): Artifact[] {
	return [
		{
			id: `artifact-transcript-${contentId}`,
			contentId,
			processingJobId,
			type: "transcript",
			content: MOCK_TRANSCRIPT,
			createdAt: "2026-06-26T12:00:00+00:00",
		},
		{
			id: `artifact-summary-${contentId}`,
			contentId,
			processingJobId,
			type: "summary",
			content: MOCK_SUMMARY,
			createdAt: "2026-06-26T12:00:01+00:00",
		},
	];
}

export const artifactMocksByContentId: Record<string, Artifact[]> = {
	"1": buildMockArtifactsForContent("1", "1"),
};
