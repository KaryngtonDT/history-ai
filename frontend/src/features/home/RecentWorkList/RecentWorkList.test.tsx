import { render, screen } from "@testing-library/react";
import { MemoryRouter } from "react-router";
import { describe, expect, it } from "vitest";
import type { WorkItem } from "@/services/workItem/types";
import { RecentWorkList } from "./RecentWorkList";

const SAMPLE: WorkItem[] = [
	{
		id: "1",
		type: "pdf",
		title: "The Roman Empire",
		status: "processing",
		progress: 62,
		currentStep: "Processing document",
		openRoute: "/processing/1",
		primaryActionLabel: "Resume",
		primaryActionRoute: "/processing/1",
		icon: "📄",
		description: "PDF",
		capabilities: [],
		updatedAt: "2026-01-01T00:00:00Z",
	},
];

describe("RecentWorkList", () => {
	it("renders open links with valid routes", () => {
		render(
			<MemoryRouter>
				<RecentWorkList items={SAMPLE} />
			</MemoryRouter>,
		);

		const link = screen.getByRole("link", { name: "Open The Roman Empire" });
		expect(link).toHaveAttribute("href", "/processing/1");
	});
});
