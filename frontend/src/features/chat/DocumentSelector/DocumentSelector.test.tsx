import { readFileSync } from "node:fs";
import { join } from "node:path";
import { render, screen } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { DocumentSelector } from "./DocumentSelector";

const availableDocuments = [
	{
		contentId: "550e8400-e29b-41d4-a716-446655440000",
		label: "This document",
	},
	{
		contentId: "550e8400-e29b-41d4-a716-446655440099",
		label: "550e8400-e29b-41d4-a716-446655440099",
	},
];

describe("DocumentSelector", () => {
	it("renders checked documents", () => {
		render(
			<DocumentSelector
				availableDocuments={availableDocuments}
				selectedContentIds={["550e8400-e29b-41d4-a716-446655440000"]}
				onSelectionChange={vi.fn()}
			/>,
		);

		const checkboxes = screen.getAllByRole("checkbox");
		expect(checkboxes[0]).toBeChecked();
		expect(checkboxes[1]).not.toBeChecked();
	});

	it("emits updated content ids when a document is selected", async () => {
		const user = userEvent.setup();
		const onSelectionChange = vi.fn();

		render(
			<DocumentSelector
				availableDocuments={availableDocuments}
				selectedContentIds={["550e8400-e29b-41d4-a716-446655440000"]}
				onSelectionChange={onSelectionChange}
			/>,
		);

		await user.click(
			screen.getByLabelText("550e8400-e29b-41d4-a716-446655440099"),
		);

		expect(onSelectionChange).toHaveBeenCalledWith([
			"550e8400-e29b-41d4-a716-446655440000",
			"550e8400-e29b-41d4-a716-446655440099",
		]);
	});

	it("does not emit an empty selection when unchecking the only document", async () => {
		const user = userEvent.setup();
		const onSelectionChange = vi.fn();

		render(
			<DocumentSelector
				availableDocuments={availableDocuments}
				selectedContentIds={["550e8400-e29b-41d4-a716-446655440000"]}
				onSelectionChange={onSelectionChange}
			/>,
		);

		const onlyCheckbox = screen.getByLabelText("This document");
		expect(onlyCheckbox).toBeDisabled();
		await user.click(onlyCheckbox);

		expect(onSelectionChange).not.toHaveBeenCalled();
	});

	it("does not import repository or service modules", () => {
		const source = readFileSync(
			join(__dirname, "DocumentSelector.tsx"),
			"utf8",
		);

		expect(source).not.toContain("ConversationService");
		expect(source).not.toContain("HttpConversationRepository");
		expect(source).not.toContain("ConversationRepository");
	});
});
