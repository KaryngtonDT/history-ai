import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { ChatMessage } from "./ChatMessage";

describe("ChatMessage", () => {
	it("renders user message content", () => {
		render(<ChatMessage speaker="user" content="Why did Rome collapse?" />);

		expect(screen.getByText("Why did Rome collapse?")).toBeInTheDocument();
		expect(screen.getByLabelText("You")).toBeInTheDocument();
	});

	it("renders assistant message content", () => {
		render(
			<ChatMessage
				speaker="assistant"
				content="Mock answer based on retrieved context."
			/>,
		);

		expect(
			screen.getByText("Mock answer based on retrieved context."),
		).toBeInTheDocument();
		expect(screen.getByLabelText("Assistant")).toBeInTheDocument();
	});

	it("shows generating state for streaming assistant message", () => {
		render(<ChatMessage speaker="assistant" content="" streaming />);

		expect(screen.getByText("Generating...")).toBeInTheDocument();
		expect(screen.getByLabelText("Assistant")).toHaveAttribute(
			"aria-busy",
			"true",
		);
	});

	it("renders citation markers as buttons and emits chunkId on click", async () => {
		const user = userEvent.setup();
		const onCitationClick = vi.fn();

		render(
			<ChatMessage
				speaker="assistant"
				content="Rome collapsed because of military pressure [1]."
				citations={[
					{
						number: 1,
						artifactId: "550e8400-e29b-41d4-a716-446655440002",
						chunkId: "550e8400-e29b-41d4-a716-446655440010",
					},
				]}
				onCitationClick={onCitationClick}
			/>,
		);

		await user.click(screen.getByRole("button", { name: "Citation 1" }));

		expect(onCitationClick).toHaveBeenCalledWith({
			artifactId: "550e8400-e29b-41d4-a716-446655440002",
			chunkId: "550e8400-e29b-41d4-a716-446655440010",
		});
	});

	it("does not import services directly", () => {
		const source = readFileSync(join(__dirname, "ChatMessage.tsx"), "utf8");

		expect(source).not.toContain("@/services/");
	});
});
