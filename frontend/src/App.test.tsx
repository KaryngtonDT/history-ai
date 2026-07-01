import { screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { AppRouter } from "@/app/router";
import { renderWithProviders } from "@/test/render";

describe("Sprint 1 — app shell", () => {
	it("renders layout and navigates between pages", async () => {
		const user = userEvent.setup();

		renderWithProviders(
			<MemoryRouter initialEntries={["/"]}>
				<AppRouter />
			</MemoryRouter>,
		);

		expect(
			screen.getByText("AI video and knowledge localization platform."),
		).toBeInTheDocument();
		expect(
			screen.getByText("Transform knowledge into understanding."),
		).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getByText("Recent work")).toBeInTheDocument();
		});
		expect(screen.getAllByText("The Roman Empire").length).toBeGreaterThan(0);

		await user.click(screen.getByRole("link", { name: /Import Documents/i }));
		expect(screen.getByRole("heading", { name: "Import" })).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Select PDF" }),
		).toBeInTheDocument();

		await user.click(screen.getByRole("link", { name: /Library/i }));
		expect(
			screen.getByRole("heading", { name: "Library" }),
		).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getAllByText("The Roman Empire").length).toBeGreaterThan(0);
		});

		await user.click(screen.getByRole("link", { name: /Settings/i }));
		expect(
			screen.getByRole("heading", { name: "Settings" }),
		).toBeInTheDocument();
	});
});
