import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { ChatMessageList } from "./ChatMessageList";

describe("ChatMessageList", () => {
	it("renders user and assistant messages", () => {
		render(
			<ChatMessageList
				messages={[
					{
						id: "1",
						role: "user",
						content: "Why did Rome collapse?",
					},
					{
						id: "2",
						role: "assistant",
						content: "Mock answer based on retrieved context.",
					},
				]}
				artifactTypesById={{}}
			/>,
		);

		expect(screen.getByText("Why did Rome collapse?")).toBeInTheDocument();
		expect(
			screen.getByText("Mock answer based on retrieved context."),
		).toBeInTheDocument();
	});

	it("renders sources for assistant messages", () => {
		render(
			<ChatMessageList
				messages={[
					{
						id: "2",
						role: "assistant",
						content: "Mock answer based on retrieved context [1].",
						sources: [
							{
								artifactId: "550e8400-e29b-41d4-a716-446655440002",
								chunkId: "550e8400-e29b-41d4-a716-446655440010",
								text: "## Ancient Rome",
								score: 0.97,
							},
						],
						citations: [
							{
								number: 1,
								artifactId: "550e8400-e29b-41d4-a716-446655440002",
								chunkId: "550e8400-e29b-41d4-a716-446655440010",
								score: 0.97,
							},
						],
					},
				]}
				artifactTypesById={{
					"550e8400-e29b-41d4-a716-446655440002": "summary",
				}}
				onCitationClick={vi.fn()}
			/>,
		);

		expect(screen.getByText("Sources")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "[1] Summary (0.97)" }),
		).toBeInTheDocument();
	});

	it("forwards citation clicks from answer markers", async () => {
		const user = userEvent.setup();
		const onCitationClick = vi.fn();

		render(
			<ChatMessageList
				messages={[
					{
						id: "2",
						role: "assistant",
						content: "Rome collapsed because of military pressure [1].",
						sources: [
							{
								artifactId: "550e8400-e29b-41d4-a716-446655440002",
								chunkId: "550e8400-e29b-41d4-a716-446655440010",
								text: "## Ancient Rome",
								score: 0.97,
							},
						],
						citations: [
							{
								number: 1,
								artifactId: "550e8400-e29b-41d4-a716-446655440002",
								chunkId: "550e8400-e29b-41d4-a716-446655440010",
								score: 0.97,
							},
						],
					},
				]}
				artifactTypesById={{
					"550e8400-e29b-41d4-a716-446655440002": "summary",
				}}
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
		const source = readFileSync(join(__dirname, "ChatMessageList.tsx"), "utf8");

		expect(source).not.toContain("ChatService");
		expect(source).not.toContain("HttpChatRepository");
	});
});
