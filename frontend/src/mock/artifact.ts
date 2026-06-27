import type { Artifact } from "@/services/artifact/types";
import { generateSummaryFromTranscript } from "./summaryGenerator";

export const MOCK_TRANSCRIPT =
	"The Roman Empire was vast. It lasted many centuries. Its legacy shaped Europe. Modern law still reflects Roman ideas. Archaeology continues to reveal new sites.";

export const MOCK_SUMMARY = generateSummaryFromTranscript(MOCK_TRANSCRIPT);

export const MOCK_TIMELINE = [
	"# Timeline",
	"",
	"## Ancient Rome",
	"",
	"- 753 BC — Foundation of Rome",
	"- Republic established",
	"",
	"## Empire",
	"",
	"- Augustus becomes emperor",
	"- Pax Romana",
].join("\n");

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
	"content-1": [
		{
			id: "artifact-1",
			contentId: "content-1",
			processingJobId: "job-1",
			type: "summary",
			content: MOCK_SUMMARY,
			createdAt: "2026-06-26T12:00:01+00:00",
		},
	],
	"content-2": [
		{
			id: "artifact-2",
			contentId: "content-2",
			processingJobId: "job-2",
			type: "quiz",
			content: [
				"# Quiz",
				"",
				"## Question 1",
				"What was the capital of the Roman Empire?",
				"- A) Athens",
				"- B) Rome",
				"- C) Carthage",
				"- D) Alexandria",
				"",
				"Answer: B",
			].join("\n"),
			createdAt: "2026-06-26T12:00:02+00:00",
		},
	],
	"content-3": [
		{
			id: "artifact-3",
			contentId: "content-3",
			processingJobId: "job-3",
			type: "flashcards",
			content: [
				"# Flashcards",
				"",
				"## Card 1",
				"",
				"Front:",
				"What was the capital of the Roman Empire?",
				"",
				"Back:",
				"Rome",
			].join("\n"),
			createdAt: "2026-06-26T12:00:03+00:00",
		},
	],
	"content-4": [
		{
			id: "artifact-4",
			contentId: "content-4",
			processingJobId: "job-4",
			type: "timeline",
			content: MOCK_TIMELINE,
			createdAt: "2026-06-26T12:00:04+00:00",
		},
	],
};
