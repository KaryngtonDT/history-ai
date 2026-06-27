import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { ProcessingQuiz } from "@/features/processing/ProcessingQuiz";
import type { Artifact } from "@/services/artifact/types";

const quizArtifact: Artifact = {
	id: "artifact-quiz-1",
	contentId: "content-1",
	processingJobId: "job-1",
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
};

describe("ProcessingQuiz", () => {
	it("displays quiz artifact content with readable markdown structure", () => {
		render(<ProcessingQuiz artifact={quizArtifact} />);

		expect(screen.getByText("Quiz")).toBeInTheDocument();
		expect(
			screen.getByRole("heading", { name: "Question 1" }),
		).toBeInTheDocument();
		expect(
			screen.getByText("What was the capital of the Roman Empire?"),
		).toBeInTheDocument();
		expect(screen.getByText("- B) Rome")).toBeInTheDocument();
		expect(screen.getByText("Answer: B")).toBeInTheDocument();
	});

	it("shows empty state when no quiz artifact is provided", () => {
		render(<ProcessingQuiz artifact={null} />);

		expect(screen.getByText("Quiz")).toBeInTheDocument();
		expect(screen.getByText("No quiz yet")).toBeInTheDocument();
	});
});
