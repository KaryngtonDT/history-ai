import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { AppRouter } from "@/app/router";

describe("Sprint 1 — app shell", () => {
	it("renders layout and navigates between pages", async () => {
		const user = userEvent.setup();

		render(
			<MemoryRouter initialEntries={["/"]}>
				<AppRouter />
			</MemoryRouter>,
		);

		expect(screen.getByText("Knowledge Operating System")).toBeInTheDocument();
		expect(
			screen.getByText("Transform knowledge into understanding."),
		).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getByText("Recent Content")).toBeInTheDocument();
		});

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.click(screen.getByRole("link", { name: "Import" }));
		expect(screen.getByRole("heading", { name: "Import" })).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Select PDF" }),
		).toBeInTheDocument();

		await user.click(screen.getByRole("link", { name: "Library" }));
		expect(
			screen.getByRole("heading", { name: "Library" }),
		).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		await user.click(screen.getByRole("link", { name: "Settings" }));
		expect(
			screen.getByRole("heading", { name: "Settings" }),
		).toBeInTheDocument();
	});
});
