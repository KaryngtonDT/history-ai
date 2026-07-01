import { screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { MOCK_PREVIEW_SCHEDULE } from "@/services/scheduler/MockSchedulerRepository";
import { renderWithProviders } from "@/test/render";
import { ProcessingResourceMonitor } from "./ProcessingResourceMonitor";

describe("ProcessingResourceMonitor", () => {
	it("renders queue badges, current stage, and timeline", () => {
		renderWithProviders(
			<ProcessingResourceMonitor schedule={MOCK_PREVIEW_SCHEDULE} />,
		);

		expect(screen.getByText("Processing Resources")).toBeInTheDocument();
		expect(screen.getByText("Balanced")).toBeInTheDocument();
		expect(screen.getByText("GPU Queue")).toBeInTheDocument();
		expect(screen.getByText("1 running / 2 pending")).toBeInTheDocument();
		expect(screen.getByText("Current stage")).toBeInTheDocument();
		expect(screen.getAllByText("Voice Clone").length).toBeGreaterThan(0);
		expect(screen.getByText("Running")).toBeInTheDocument();
	});

	it("shows fallback when schedule is unavailable", () => {
		renderWithProviders(<ProcessingResourceMonitor schedule={null} />);

		expect(
			screen.getByText(/Schedule preview unavailable/i),
		).toBeInTheDocument();
	});
});
