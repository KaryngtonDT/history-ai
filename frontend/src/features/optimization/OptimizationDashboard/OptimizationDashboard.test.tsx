import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { MOCK_PREVIEW_OPTIMIZATION } from "@/services/optimization/MockOptimizationRepository";
import { renderWithProviders } from "@/test/render";
import { OptimizationDashboard } from "./OptimizationDashboard";

describe("OptimizationDashboard", () => {
	it("renders optimization profile, parameters, and explanations", () => {
		renderWithProviders(
			<OptimizationDashboard optimization={MOCK_PREVIEW_OPTIMIZATION} />,
		);

		expect(screen.getByText("Automatic Optimization")).toBeInTheDocument();
		expect(screen.getByText("Quality")).toBeInTheDocument();
		expect(screen.getByText("Speech Recognition")).toBeInTheDocument();
		expect(screen.getByText("Beam Size")).toBeInTheDocument();
		expect(screen.getByText("5")).toBeInTheDocument();
		expect(
			screen.getByText("Low STT confidence: beam size increased to 5."),
		).toBeInTheDocument();
	});

	it("shows loading state", () => {
		renderWithProviders(<OptimizationDashboard optimization={null} loading />);

		expect(
			screen.getByText("Calculating execution optimization..."),
		).toBeInTheDocument();
	});
});
