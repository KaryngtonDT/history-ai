import { render, screen } from "@testing-library/react";
import { describe, expect, it } from "vitest";
import { createTimeline } from "@/domain/timeline";
import { createTimelineEvent } from "@/domain/timeline/TimelineEvent";
import { createTimelineSection } from "@/domain/timeline/TimelineSection";
import { InteractiveTimeline } from "./InteractiveTimeline";

const timeline = createTimeline([
	createTimelineSection("Ancient Rome", [
		createTimelineEvent("753 BC — Foundation of Rome"),
		createTimelineEvent("Republic established"),
	]),
	createTimelineSection("Empire", [
		createTimelineEvent("Augustus becomes emperor"),
		createTimelineEvent("Pax Romana"),
	]),
]);

describe("InteractiveTimeline", () => {
	it("renders sections and events in order", () => {
		render(<InteractiveTimeline timeline={timeline} />);

		const sectionHeadings = screen.getAllByRole("heading", { level: 3 });
		expect(sectionHeadings.map((heading) => heading.textContent)).toEqual([
			"Ancient Rome",
			"Empire",
		]);

		const eventItems = screen.getAllByRole("listitem");
		expect(eventItems.map((item) => item.textContent)).toEqual([
			"753 BC — Foundation of Rome",
			"Republic established",
			"Augustus becomes emperor",
			"Pax Romana",
		]);
	});

	it("shows empty message when timeline has no sections", () => {
		render(<InteractiveTimeline timeline={createTimeline([])} />);

		expect(
			screen.getByText("No timeline events to display."),
		).toBeInTheDocument();
	});
});
