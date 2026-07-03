import { screen, waitFor } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import { SecondBrainCenter } from "@/features/shadowBrain/SecondBrainCenter";
import { renderWithProviders } from "@/test/render";

describe("SecondBrainCenter", () => {
	it("renders second brain workspace from mock service", async () => {
		renderWithProviders(
			<MemoryRouter initialEntries={["/settings/shadow/brain"]}>
				<SecondBrainCenter />
			</MemoryRouter>,
		);

		await waitFor(() => {
			expect(screen.getByText("Knowledge Explorer")).toBeInTheDocument();
		});

		expect(screen.getByText("8 videos")).toBeInTheDocument();
		expect(screen.getAllByText("Personal Notes").length).toBeGreaterThan(0);
	});
});
