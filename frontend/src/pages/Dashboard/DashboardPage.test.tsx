import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter, Route, Routes } from "react-router";
import { describe, expect, it } from "vitest";
import { DashboardPage } from "@/pages/Dashboard/DashboardPage";
import { ImportPage } from "@/pages/Import/ImportPage";

describe("DashboardPage — S1-SLICE-03A composition", () => {
	it("renders dashboard sections with placeholders", () => {
		render(
			<MemoryRouter>
				<DashboardPage />
			</MemoryRouter>,
		);

		expect(
			screen.getByText("Transform knowledge into understanding."),
		).toBeInTheDocument();
		expect(screen.getByText("Recent Content")).toBeInTheDocument();
		expect(screen.getByText("Statistics")).toBeInTheDocument();
		expect(screen.getAllByText("Loading...")).toHaveLength(2);
		expect(
			screen.getByRole("button", { name: "Import PDF" }),
		).toBeInTheDocument();
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
