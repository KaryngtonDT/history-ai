import { screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { renderWithProviders } from "@/test/render";
import { ProcessingModeSelector } from "./ProcessingModeSelector";

describe("ProcessingModeSelector", () => {
	it("renders manual and automatic options", () => {
		renderWithProviders(
			<ProcessingModeSelector mode="manual" onChange={vi.fn()} />,
		);

		expect(screen.getByText("Processing Mode")).toBeInTheDocument();
		expect(screen.getByRole("radio", { name: /Manual/ })).toBeInTheDocument();
		expect(
			screen.getByRole("radio", { name: /Automatic/ }),
		).toBeInTheDocument();
	});

	it("calls onChange when automatic is selected", async () => {
		const user = userEvent.setup();
		const onChange = vi.fn();

		renderWithProviders(
			<ProcessingModeSelector mode="manual" onChange={onChange} />,
		);

		await user.click(screen.getByRole("radio", { name: /Automatic/ }));

		expect(onChange).toHaveBeenCalledWith("automatic");
	});
});
