import { describe, expect, it } from "vitest";
import { MockTimelineRepository } from "./MockTimelineRepository";

describe("MockTimelineRepository", () => {
	it("returns structured timeline for mock timeline artifact", async () => {
		const repository = new MockTimelineRepository();

		const timeline = await repository.getTimeline("artifact-4");

		expect(timeline).not.toBeNull();
		expect(timeline?.sections.length).toBeGreaterThan(0);
		expect(timeline?.sections[0]?.title).toBe("Ancient Rome");
		expect(timeline?.sections[0]?.events[0]?.text).toBe(
			"753 BC — Foundation of Rome",
		);
	});

	it("returns null when mock timeline artifact does not exist", async () => {
		const repository = new MockTimelineRepository();

		const timeline = await repository.getTimeline("missing-artifact");

		expect(timeline).toBeNull();
	});

	it("returns null for non-timeline mock artifacts", async () => {
		const repository = new MockTimelineRepository();

		const timeline = await repository.getTimeline("artifact-1");

		expect(timeline).toBeNull();
	});
});
