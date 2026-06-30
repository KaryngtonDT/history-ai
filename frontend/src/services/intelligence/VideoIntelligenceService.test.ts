import { describe, expect, it } from "vitest";
import { MOCK_PREVIEW_INTELLIGENCE } from "./MockVideoIntelligenceRepository";
import { VideoIntelligenceService } from "./VideoIntelligenceService";

describe("VideoIntelligenceService", () => {
	it("formats duration and confidence", () => {
		const service = new VideoIntelligenceService({
			getPreviewIntelligence: async () => MOCK_PREVIEW_INTELLIGENCE,
			getByVideoId: async () => MOCK_PREVIEW_INTELLIGENCE,
		});

		expect(service.formatDuration(762)).toBe("12m 42s");
		expect(service.formatConfidence(97)).toBe("97%");
	});
});
