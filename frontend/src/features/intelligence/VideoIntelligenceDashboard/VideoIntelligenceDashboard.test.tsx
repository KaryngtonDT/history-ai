import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { MOCK_PREVIEW_INTELLIGENCE } from "@/services/intelligence/MockVideoIntelligenceRepository";
import { renderWithProviders } from "@/test/render";
import { VideoIntelligenceDashboard } from "./VideoIntelligenceDashboard";

describe("VideoIntelligenceDashboard", () => {
	it("renders intelligence metrics and recommendation reasons", () => {
		renderWithProviders(
			<VideoIntelligenceDashboard
				intelligence={MOCK_PREVIEW_INTELLIGENCE}
				recommendation={{
					id: "rec-1",
					strategy: "quality",
					explanation: "Quality-first pipeline.",
					estimatedDurationSeconds: 300,
					estimatedQuality: 5,
					estimatedVramGb: 12,
					reasons: ["Two speakers detected.", "High STT confidence."],
					stages: [],
				}}
			/>,
		);

		expect(screen.getByText("Video Intelligence")).toBeInTheDocument();
		expect(screen.getByText("english")).toBeInTheDocument();
		expect(screen.getByText("Two speakers detected.")).toBeInTheDocument();
		expect(screen.getByText("97%")).toBeInTheDocument();
	});
});
