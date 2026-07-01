import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { MOCK_PREVIEW_HISTORY } from "@/services/history/MockHistoryRepository";
import { renderWithProviders as render } from "@/test/render";
import { ExecutionComparison } from "../ExecutionComparison";
import { VersionTimeline } from "../VersionTimeline";

describe("History feature components", () => {
	it("renders version timeline entries", () => {
		render(
			<VersionTimeline
				versions={MOCK_PREVIEW_HISTORY.versions}
				selectedVersion={2}
				onSelect={() => {}}
			/>,
		);

		expect(screen.getByText("V2")).toBeInTheDocument();
		expect(screen.getByText("Score 96")).toBeInTheDocument();
		expect(screen.getByText("Quality")).toBeInTheDocument();
	});

	it("renders comparison summary", () => {
		render(
			<ExecutionComparison
				comparison={{
					leftVersion: 1,
					rightVersion: 2,
					providerDifferences: [
						{
							stage: "translation",
							leftProvider: "ollama",
							rightProvider: "mock",
						},
					],
					optimizationDifference: {
						leftProfile: "balanced",
						rightProfile: "quality",
						changedParameters: ["speech_to_text.beamSize"],
					},
					qualityScoreDifference: {
						leftScore: 91,
						rightScore: 96,
						delta: 5,
					},
				}}
			/>,
		);

		expect(screen.getByText("Compare V1 vs V2")).toBeInTheDocument();
		expect(screen.getByText(/ollama → mock/)).toBeInTheDocument();
		expect(screen.getByText(/91 → 96/)).toBeInTheDocument();
	});
});
