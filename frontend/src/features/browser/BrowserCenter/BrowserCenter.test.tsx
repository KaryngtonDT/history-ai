import { screen, waitFor } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { BrowserCenter } from "@/features/browser/BrowserCenter";
import { renderWithProviders } from "@/test/render";

describe("BrowserCenter", () => {
	it("renders browser settings from mock service", async () => {
		renderWithProviders(
			<MemoryRouter>
				<BrowserCenter />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Browser session")).toBeInTheDocument();
		});

		expect(screen.getByText("Platform inspector")).toBeInTheDocument();
		expect(screen.getByText("Activity history")).toBeInTheDocument();
		expect(screen.getByText("Reading companion demo")).toBeInTheDocument();
	});
});
