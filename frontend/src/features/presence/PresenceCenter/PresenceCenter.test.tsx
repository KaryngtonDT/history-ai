import { screen, waitFor } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { PresenceCenter } from "@/features/presence/PresenceCenter";
import { renderWithProviders } from "@/test/render";

describe("PresenceCenter", () => {
	it("renders presence settings from mock service", async () => {
		renderWithProviders(
			<MemoryRouter>
				<PresenceCenter />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Connection status")).toBeInTheDocument();
		});

		expect(screen.getByText("Companion surfaces")).toBeInTheDocument();
		expect(screen.getByText("Activity log")).toBeInTheDocument();
	});
});
