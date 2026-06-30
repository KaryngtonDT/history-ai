import { describe, expect, it } from "vitest";
import { MockOrchestratorRepository } from "./MockOrchestratorRepository";
import { OrchestratorService } from "./OrchestratorService";

describe("OrchestratorService", () => {
	it("loads recommendation from repository", async () => {
		const service = new OrchestratorService(new MockOrchestratorRepository());
		const recommendation = await service.loadRecommendation();

		expect(recommendation.strategy).toBe("balanced");
		expect(recommendation.stages).toHaveLength(6);
	});

	it("formats estimated duration and quality", () => {
		const service = new OrchestratorService(new MockOrchestratorRepository());

		expect(service.formatEstimatedDuration(240)).toBe("4 min");
		expect(service.formatQualityStars(4)).toBe("★★★★");
	});
});
