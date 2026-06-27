import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import type { Artifact } from "@/services/artifact/types";
import {
	ARTIFACT_DISPLAY_ORDER,
	getArtifactRenderer,
} from "./ArtifactRendererRegistry";
import { FlashcardsArtifactRenderer } from "./FlashcardsArtifactRenderer";
import { QuizArtifactRenderer } from "./QuizArtifactRenderer";
import { SummaryArtifactRenderer } from "./SummaryArtifactRenderer";
import { TranscriptArtifactRenderer } from "./TranscriptArtifactRenderer";
import { UnsupportedArtifactRenderer } from "./UnsupportedArtifactRenderer";

const unsupportedArtifact: Artifact = {
	id: "artifact-timeline-1",
	contentId: "content-1",
	processingJobId: "job-1",
	type: "timeline",
	content: "Timeline content preview",
	createdAt: "2026-06-26T12:00:04+00:00",
};

describe("ArtifactRendererRegistry", () => {
	it("returns summary renderer", () => {
		expect(getArtifactRenderer("summary")).toBe(SummaryArtifactRenderer);
	});

	it("returns transcript renderer", () => {
		expect(getArtifactRenderer("transcript")).toBe(TranscriptArtifactRenderer);
	});

	it("returns quiz renderer", () => {
		expect(getArtifactRenderer("quiz")).toBe(QuizArtifactRenderer);
	});

	it("returns flashcards renderer", () => {
		expect(getArtifactRenderer("flashcards")).toBe(FlashcardsArtifactRenderer);
	});

	it("returns unsupported renderer for unknown types", () => {
		expect(getArtifactRenderer("timeline")).toBe(UnsupportedArtifactRenderer);
	});

	it("renders unsupported type with fallback card", () => {
		const Renderer = getArtifactRenderer("timeline");

		render(<Renderer artifact={unsupportedArtifact} />);

		expect(screen.getByText("timeline")).toBeInTheDocument();
		expect(
			screen.getByText(
				"This artifact type is not yet supported in the viewer.",
			),
		).toBeInTheDocument();
		expect(screen.getByText("Timeline content preview")).toBeInTheDocument();
	});

	it("defines display order for known artifact types", () => {
		expect(ARTIFACT_DISPLAY_ORDER).toEqual([
			"summary",
			"transcript",
			"quiz",
			"flashcards",
		]);
	});
});
