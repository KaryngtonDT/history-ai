import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { AppRouter } from "@/app/router";

describe("Sprint 1 Slice 1 — app shell", () => {
	it("renders layout and navigates between pages", async () => {
		const user = userEvent.setup();

		render(
			<MemoryRouter initialEntries={["/"]}>
				<AppRouter />
			</MemoryRouter>,
		);

		expect(screen.getByText("Knowledge Operating System")).toBeInTheDocument();
		expect(
			screen.getByRole("heading", { name: "Dashboard" }),
		).toBeInTheDocument();
		expect(screen.getByText("Welcome to History AI")).toBeInTheDocument();

		await user.click(screen.getByRole("link", { name: "Import" }));
		expect(screen.getByRole("heading", { name: "Import" })).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Import PDF" }),
		).toBeInTheDocument();

		await user.click(screen.getByRole("link", { name: "Library" }));
		expect(
			screen.getByRole("heading", { name: "Library" }),
		).toBeInTheDocument();
		expect(screen.getByText("No content yet")).toBeInTheDocument();

		await user.click(screen.getByRole("link", { name: "Settings" }));
		expect(
			screen.getByRole("heading", { name: "Settings" }),
		).toBeInTheDocument();
	});
});
