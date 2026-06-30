import { describe, expect, it, vi } from "vitest";
import { MOCK_PREVIEW_OPTIMIZATION } from "./MockOptimizationRepository";
import type { OptimizationRepository } from "./OptimizationRepository";
import { OptimizationService } from "./OptimizationService";

function createRepositoryMock(
	overrides: Partial<OptimizationRepository> = {},
): OptimizationRepository {
	return {
		getPreviewOptimization: vi
			.fn()
			.mockResolvedValue(MOCK_PREVIEW_OPTIMIZATION),
		getByVideoId: vi.fn().mockResolvedValue(MOCK_PREVIEW_OPTIMIZATION),
		...overrides,
	};
}

describe("OptimizationService", () => {
	it("loads preview optimization from repository", async () => {
		const getPreviewOptimization = vi
			.fn()
			.mockResolvedValue(MOCK_PREVIEW_OPTIMIZATION);
		const service = new OptimizationService(
			createRepositoryMock({ getPreviewOptimization }),
		);

		const result = await service.loadPreviewOptimization();

		expect(getPreviewOptimization).toHaveBeenCalledOnce();
		expect(result).toEqual(MOCK_PREVIEW_OPTIMIZATION);
	});

	it("loads optimization by video id", async () => {
		const getByVideoId = vi.fn().mockResolvedValue({
			...MOCK_PREVIEW_OPTIMIZATION,
			videoId: "550e8400-e29b-41d4-a716-446655440099",
		});
		const service = new OptimizationService(
			createRepositoryMock({ getByVideoId }),
		);

		const result = await service.loadByVideoId(
			"550e8400-e29b-41d4-a716-446655440099",
		);

		expect(getByVideoId).toHaveBeenCalledWith(
			"550e8400-e29b-41d4-a716-446655440099",
		);
		expect(result.videoId).toBe("550e8400-e29b-41d4-a716-446655440099");
	});

	it("formats profile, stage, and parameter labels", () => {
		const service = new OptimizationService(createRepositoryMock());

		expect(service.formatProfile("quality")).toBe("Quality");
		expect(service.formatStageLabel("speech_to_text")).toBe(
			"Speech Recognition",
		);
		expect(service.formatParameterLabel("beamSize")).toBe("Beam Size");
	});

	it("returns primary parameters for a stage", () => {
		const service = new OptimizationService(createRepositoryMock());
		const stage = MOCK_PREVIEW_OPTIMIZATION.stages[0];

		expect(service.primaryParameters(stage)).toEqual(stage.parameters);
	});
});
