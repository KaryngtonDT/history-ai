import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { Button } from "@/components/ui/Button";
import styles from "./Button.module.css";

describe("Button", () => {
	it("renders children", () => {
		render(<Button>Import PDF</Button>);
		expect(
			screen.getByRole("button", { name: "Import PDF" }),
		).toBeInTheDocument();
	});

	it("applies variant and size", () => {
		render(
			<Button variant="secondary" size="lg">
				Action
			</Button>,
		);
		const button = screen.getByRole("button", { name: "Action" });
		expect(button.className).toContain(styles.secondary);
		expect(button.className).toContain(styles.lg);
	});

	it("respects disabled state", () => {
		render(<Button disabled>Disabled</Button>);
		expect(screen.getByRole("button", { name: "Disabled" })).toBeDisabled();
	});
});
