import { render, screen, waitFor } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { LibraryPage } from "@/pages/Library/LibraryPage";

describe("LibraryPage — S1-SLICE-04 mock data", () => {
	it("renders library contents from the service layer", async () => {
		render(
			<MemoryRouter>
				<LibraryPage />
			</MemoryRouter>,
		);

		expect(
			screen.getByRole("heading", { name: "Library" }),
		).toBeInTheDocument();

		await waitFor(() => {
			expect(screen.getByText("The Roman Empire")).toBeInTheDocument();
		});

		expect(screen.getAllByText("PDF")).toHaveLength(2);
		expect(screen.getByText("YouTube")).toBeInTheDocument();
		expect(screen.getByRole("progressbar")).toHaveAttribute(
			"aria-valuenow",
			"62",
		);
	});
});
