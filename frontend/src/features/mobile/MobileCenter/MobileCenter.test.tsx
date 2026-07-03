import { screen, waitFor } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { MobileCenter } from "@/features/mobile/MobileCenter/MobileCenter";
import { renderWithProviders } from "@/test/render";

describe("MobileCenter", () => {
	it("renders mobile companion settings from mock service", async () => {
		renderWithProviders(
			<MemoryRouter>
				<MobileCenter />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Mobile connection")).toBeInTheDocument();
		});

		expect(screen.getByText("Today")).toBeInTheDocument();
		expect(screen.getByText("Connections")).toBeInTheDocument();
		expect(screen.getByText("Personal server")).toBeInTheDocument();
	});
});
