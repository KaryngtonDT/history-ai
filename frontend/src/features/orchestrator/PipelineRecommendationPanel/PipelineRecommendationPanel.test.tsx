import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { PipelineRecommendationPanel } from "./PipelineRecommendationPanel";

describe("PipelineRecommendationPanel", () => {
	it("renders recommendation metrics", () => {
		render(
			<PipelineRecommendationPanel
				recommendation={{
					id: "550e8400-e29b-41d4-a716-446655440099",
					strategy: "balanced",
					explanation: "Balanced pipeline for English content.",
					estimatedDurationSeconds: 240,
					estimatedQuality: 4,
					estimatedVramGb: 8,
					stages: [],
				}}
			/>,
		);

		expect(screen.getByText("Pipeline Recommendation")).toBeInTheDocument();
		expect(screen.getByText("Balanced")).toBeInTheDocument();
		expect(screen.getByText("4 min")).toBeInTheDocument();
		expect(screen.getByText("★★★★")).toBeInTheDocument();
	});
});
