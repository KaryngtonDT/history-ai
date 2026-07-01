import { screen, waitFor } from "@testing-library/react";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { MOCK_SUMMARY, MOCK_TRANSCRIPT } from "@/mock/artifact";
import { ProcessingPage } from "@/pages/Processing/ProcessingPage";
import { renderWithProviders as render } from "@/test/render";

describe("ProcessingPage — S1-SLICE-06 simulated progress", () => {
	it("renders processing simulation for a known job", async () => {
		vi.useFakeTimers({ shouldAdvanceTime: true });

		render(
			<MemoryRouter initialEntries={["/processing/1"]}>
				<Routes>
					<Route path="/processing/:id" element={<ProcessingPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(
				screen.getByRole("heading", { name: "Processing" }),
			).toBeInTheDocument();
		});

		expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		expect(screen.getByRole("progressbar")).toHaveAttribute(
			"aria-valuenow",
			"0",
		);

		await vi.runAllTimersAsync();

		await waitFor(() => {
			expect(screen.getByText("Processing complete")).toBeInTheDocument();
		});

		expect(screen.getByText("Ready")).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getByText(MOCK_SUMMARY)).toBeInTheDocument();
		});

		await waitFor(() => {
			expect(screen.getByText(MOCK_TRANSCRIPT)).toBeInTheDocument();
		});

		vi.useRealTimers();
	});

	it("shows empty state for unknown processing id", async () => {
		render(
			<MemoryRouter initialEntries={["/processing/unknown"]}>
				<Routes>
					<Route path="/processing/:id" element={<ProcessingPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Processing job not found")).toBeInTheDocument();
		});
	});
});
