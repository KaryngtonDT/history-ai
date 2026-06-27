import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { ProcessingFlashcards } from "@/features/processing/ProcessingFlashcards";
import type { Artifact } from "@/services/artifact/types";

const flashcardsArtifact: Artifact = {
	id: "artifact-flashcards-1",
	contentId: "content-1",
	processingJobId: "job-1",
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
		"",
		"---",
		"",
		"## Card 2",
		"",
		"Front:",
		"Who was the first Roman emperor?",
		"",
		"Back:",
		"Augustus",
	].join("\n"),
	createdAt: "2026-06-26T12:00:03+00:00",
};

describe("ProcessingFlashcards", () => {
	it("displays flashcards artifact content with readable markdown structure", () => {
		render(<ProcessingFlashcards artifact={flashcardsArtifact} />);

		expect(screen.getByText("Flashcards")).toBeInTheDocument();
		expect(screen.getByRole("heading", { name: "Card 1" })).toBeInTheDocument();
		expect(screen.getByRole("heading", { name: "Card 2" })).toBeInTheDocument();
		expect(
			screen.getByText("What was the capital of the Roman Empire?"),
		).toBeInTheDocument();
		expect(screen.getByText("Rome")).toBeInTheDocument();
		expect(
			screen.getByText("Who was the first Roman emperor?"),
		).toBeInTheDocument();
		expect(screen.getByText("Augustus")).toBeInTheDocument();
		expect(screen.getAllByText("Front:")).toHaveLength(2);
		expect(screen.getAllByText("Back:")).toHaveLength(2);
	});

	it("shows empty state when no flashcards artifact is provided", () => {
		render(<ProcessingFlashcards artifact={null} />);

		expect(screen.getByText("Flashcards")).toBeInTheDocument();
		expect(screen.getByText("No flashcards yet")).toBeInTheDocument();
	});
});
