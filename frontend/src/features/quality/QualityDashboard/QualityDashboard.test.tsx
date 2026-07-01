import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { MOCK_PREVIEW_QUALITY } from "@/services/quality/MockQualityRepository";
import { renderWithProviders } from "@/test/render";
import { QualityDashboard } from "./QualityDashboard";

describe("QualityDashboard", () => {
	it("renders overall score and category metrics", () => {
		renderWithProviders(<QualityDashboard report={MOCK_PREVIEW_QUALITY} />);

		expect(screen.getByText("Quality Report")).toBeInTheDocument();
		expect(screen.getByText("94")).toBeInTheDocument();
		expect(screen.getByText("Audio")).toBeInTheDocument();
		expect(screen.getByText(/Ready for publishing/)).toBeInTheDocument();
	});

	it("shows loading state", () => {
		renderWithProviders(<QualityDashboard report={null} loading />);

		expect(
			screen.getByText("Running quality assessment..."),
		).toBeInTheDocument();
	});
});
