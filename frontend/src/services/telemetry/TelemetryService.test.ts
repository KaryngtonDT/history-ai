import { describe, expect, it } from "vitest";
import { MockTelemetryRepository } from "./MockTelemetryRepository";
import { TelemetryService } from "./TelemetryService";

describe("TelemetryService", () => {
	it("loads analytics through the repository", async () => {
		const service = new TelemetryService(new MockTelemetryRepository());
		const analytics = await service.loadAnalytics("workspace-id");

		expect(analytics.processedVideos).toBe(328);
		expect(analytics.successRate).toBe(99.3);
	});

	it("extracts quality trend scores", async () => {
		const service = new TelemetryService(new MockTelemetryRepository());
		const records = await service.loadTelemetry("workspace-id");

		expect(service.qualityTrend(records)).toEqual([94]);
	});
});
