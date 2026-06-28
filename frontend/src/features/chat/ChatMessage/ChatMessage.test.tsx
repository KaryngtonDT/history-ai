import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
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

	it("does not import services directly", () => {
		const source = readFileSync(join(__dirname, "ChatMessage.tsx"), "utf8");

		expect(source).not.toContain("@/services/");
	});
});
