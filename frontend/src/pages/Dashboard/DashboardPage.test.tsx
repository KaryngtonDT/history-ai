import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it, vi } from "vitest";
import { DashboardPage } from "@/pages/Dashboard/DashboardPage";
import { ImportPage } from "@/pages/Import/ImportPage";

describe("DashboardPage — S1-SLICE-03B mock data", () => {
	it("renders dashboard data from the service layer", () => {
		render(
			<MemoryRouter>
				<DashboardPage />
			</MemoryRouter>,
		);

		expect(
			screen.getByText("Transform knowledge into understanding."),
		).toBeInTheDocument();
		expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		expect(screen.getByRole("progressbar")).toHaveAttribute(
			"aria-valuenow",
			"62",
		);
		expect(screen.getByText("French Revolution")).toBeInTheDocument();
		expect(screen.getByText("Contents")).toBeInTheDocument();
		expect(screen.getByText("12")).toBeInTheDocument();
	});

	it("logs content route on card click", async () => {
		const user = userEvent.setup();
		const logSpy = vi.spyOn(console, "log").mockImplementation(() => {});

		render(
			<MemoryRouter>
				<DashboardPage />
			</MemoryRouter>,
		);

		await user.click(screen.getByRole("button", { name: /The Roman Empire/i }));
		expect(logSpy).toHaveBeenCalledWith("/content/1");

		logSpy.mockRestore();
	});

	it("navigates quick actions to import", async () => {
		const user = userEvent.setup();

		render(
			<MemoryRouter initialEntries={["/"]}>
				<Routes>
					<Route path="/" element={<DashboardPage />} />
					<Route path="/import" element={<ImportPage />} />
				</Routes>
			</MemoryRouter>,
		);

		await user.click(screen.getByRole("button", { name: "Import Video" }));
		expect(screen.getByRole("heading", { name: "Import" })).toBeInTheDocument();
	});
});
