import { describe, expect, it } from "vitest";
import { parseTimeline } from "./TimelineParser";

describe("TimelineParser", () => {
	it("returns empty timeline for empty markdown", () => {
		expect(parseTimeline("")).toEqual({ sections: [] });
	});

	it("returns empty timeline for whitespace-only markdown", () => {
		expect(parseTimeline("   \n\n  ")).toEqual({ sections: [] });
	});

	it("parses a single section with events", () => {
		const timeline = parseTimeline(
			["## Roman Republic", "", "- 509 BC", "- Expansion"].join("\n"),
		);

		expect(timeline.sections).toHaveLength(1);
		expect(timeline.sections[0]?.title).toBe("Roman Republic");
		expect(timeline.sections[0]?.events).toEqual([
			{ text: "509 BC" },
			{ text: "Expansion" },
		]);
	});

	it("parses multiple sections in order", () => {
		const timeline = parseTimeline(
			[
				"## Roman Republic",
				"- 509 BC",
				"## Roman Empire",
				"- 27 BC",
				"- Pax Romana",
			].join("\n"),
		);

		expect(timeline.sections).toHaveLength(2);
		expect(timeline.sections[0]?.title).toBe("Roman Republic");
		expect(timeline.sections[0]?.events).toEqual([{ text: "509 BC" }]);
		expect(timeline.sections[1]?.title).toBe("Roman Empire");
		expect(timeline.sections[1]?.events).toEqual([
			{ text: "27 BC" },
			{ text: "Pax Romana" },
		]);
	});

	it("parses an empty section with no events", () => {
		const timeline = parseTimeline(
			["## Roman Republic", "", "## Roman Empire", "- 27 BC"].join("\n"),
		);

		expect(timeline.sections).toHaveLength(2);
		expect(timeline.sections[0]?.title).toBe("Roman Republic");
		expect(timeline.sections[0]?.events).toEqual([]);
		expect(timeline.sections[1]?.events).toEqual([{ text: "27 BC" }]);
	});

	it("ignores the top-level H1 heading", () => {
		const timeline = parseTimeline(
			["# Timeline", "", "## Ancient Rome", "- 753 BC"].join("\n"),
		);

		expect(timeline.sections).toHaveLength(1);
		expect(timeline.sections[0]?.title).toBe("Ancient Rome");
		expect(timeline.sections[0]?.events).toEqual([{ text: "753 BC" }]);
	});

	it("preserves event text exactly without parsing dates", () => {
		const timeline = parseTimeline(
			["## Dates", "- 509 BC — founding", "- Expansion"].join("\n"),
		);

		expect(timeline.sections[0]?.events).toEqual([
			{ text: "509 BC — founding" },
			{ text: "Expansion" },
		]);
	});

	it("preserves section and event ordering from the markdown", () => {
		const timeline = parseTimeline(
			["## First", "- Alpha", "- Beta", "## Second", "- Gamma"].join("\n"),
		);

		expect(timeline.sections.map((section) => section.title)).toEqual([
			"First",
			"Second",
		]);
		expect(timeline.sections[0]?.events.map((event) => event.text)).toEqual([
			"Alpha",
			"Beta",
		]);
		expect(timeline.sections[1]?.events.map((event) => event.text)).toEqual([
			"Gamma",
		]);
	});
});
