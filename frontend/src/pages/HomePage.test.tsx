import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { HomePage } from "@/pages/HomePage";

describe("HomePage", () => {
	it("displays the foundation message", () => {
		render(<HomePage />);

		expect(
			screen.getByRole("heading", { name: "History AI" }),
		).toBeInTheDocument();
		expect(screen.getByText("Frontend Ready")).toBeInTheDocument();
		expect(screen.getByText("React + TypeScript + Vite")).toBeInTheDocument();
	});
});
