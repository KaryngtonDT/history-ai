import { describe, expect, it } from "vitest";
import { MockMapRepository } from "./MockMapRepository";

describe("MockMapRepository", () => {
	it("returns resolved places for mock timeline artifact", async () => {
		const repository = new MockMapRepository();

		const places = await repository.getTimelineMap("artifact-4");

		expect(places).not.toBeNull();
		expect(places?.length).toBeGreaterThan(0);
		expect(places?.[0]?.name).toBe("Rome");
		expect(places?.[0]?.coordinates).toEqual({
			latitude: 41.9028,
			longitude: 12.4964,
		});
		expect(places?.[0]?.description).toBe("753 BC — Foundation of Rome");
	});

	it("returns null when mock timeline artifact does not exist", async () => {
		const repository = new MockMapRepository();

		const places = await repository.getTimelineMap("missing-artifact");

		expect(places).toBeNull();
	});

	it("returns null for non-timeline mock artifacts", async () => {
		const repository = new MockMapRepository();

		const places = await repository.getTimelineMap("artifact-1");

		expect(places).toBeNull();
	});
});
