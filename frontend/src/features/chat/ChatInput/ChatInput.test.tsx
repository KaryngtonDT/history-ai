import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { ChatInput } from "./ChatInput";

describe("ChatInput", () => {
	it("submits on Enter key", async () => {
		const user = userEvent.setup();
		const onSubmit = vi.fn();

		render(
			<ChatInput
				value="Why did Rome collapse?"
				onChange={vi.fn()}
				onSubmit={onSubmit}
			/>,
		);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"{enter}",
		);

		expect(onSubmit).toHaveBeenCalledTimes(1);
	});

	it("inserts newline on Shift+Enter without submitting", async () => {
		const user = userEvent.setup();
		const onSubmit = vi.fn();
		const onChange = vi.fn();

		render(<ChatInput value="Why" onChange={onChange} onSubmit={onSubmit} />);

		await user.type(
			screen.getByRole("textbox", { name: "Ask a question" }),
			"{shift>}{enter}{/shift}",
		);

		expect(onSubmit).not.toHaveBeenCalled();
		expect(onChange).toHaveBeenCalled();
	});

	it("disables submit button when question is empty", () => {
		render(<ChatInput value="   " onChange={vi.fn()} onSubmit={vi.fn()} />);

		expect(screen.getByRole("button", { name: "Send" })).toBeDisabled();
	});

	it("does not import services directly", () => {
		const source = readFileSync(join(__dirname, "ChatInput.tsx"), "utf8");

		expect(source).not.toContain("@/services/");
	});
});
