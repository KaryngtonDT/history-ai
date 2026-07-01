import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { renderWithProviders } from "@/test/render";
import { AnalyticsDashboard } from "./AnalyticsDashboard";

describe("AnalyticsDashboard", () => {
	it("renders workspace analytics metrics", () => {
		renderWithProviders(
			<AnalyticsDashboard
				analytics={{
					processedVideos: 328,
					averageProcessingTimeSeconds: 292,
					averageProcessingTimeLabel: "4m 52s",
					averageQuality: 94,
					successRate: 99.3,
					gpuUsagePercent: 71,
					topTranslationProvider: "Ollama",
					topTtsProvider: "F5-TTS",
					recentErrors: [],
				}}
			/>,
		);

		expect(screen.getByText("328")).toBeInTheDocument();
		expect(screen.getByText("4m 52s")).toBeInTheDocument();
		expect(screen.getByText("99.3%")).toBeInTheDocument();
	});
});
