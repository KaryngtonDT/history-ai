import { screen, waitFor } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { ExecutiveCenter } from "@/features/shadowExecutive/ExecutiveCenter";
import { renderWithProviders } from "@/test/render";

describe("ExecutiveCenter", () => {
	it("renders executive dashboard from mock service", async () => {
		renderWithProviders(<ExecutiveCenter />);

		await waitFor(() => {
			expect(screen.getByText("Today")).toBeInTheDocument();
		});

		expect(screen.getByText("Pending Decisions")).toBeInTheDocument();
		expect(screen.getByText("Review Docker fundamentals")).toBeInTheDocument();
	});
});
