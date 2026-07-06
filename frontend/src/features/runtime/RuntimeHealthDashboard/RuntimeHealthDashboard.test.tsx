import { render, screen, waitFor } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { RuntimeHealthDashboard } from "@/features/runtime/RuntimeHealthDashboard/RuntimeHealthDashboard";
import { runtimeService } from "@/services/runtime/RuntimeService";

const mockDashboard = {
	title: "Lumen Runtime Health",
	generatedAt: "2026-07-06T12:00:00+00:00",
	overallRuntimeScore: {
		score: 96,
		grade: "Excellent",
		summary: "Everything compatible with your hardware is operational.",
		breakdown: [
			{
				key: "runtime_health",
				label: "Runtime Health",
				score: 95,
				weight: 0.2,
				weightedContribution: 19,
				explanation: "Engine readiness across the catalog (95%).",
				improvement: null,
			},
		],
	},
	platformScore: {
		score: 98,
		grade: "Excellent",
		components: [
			{ key: "runtime", label: "Runtime", score: 96, status: "healthy" },
			{ key: "shadow", label: "Shadow", score: 100, status: "healthy" },
		],
	},
	summary: {
		overallHealth: 96,
		hardwareProfile: "low_end_local",
		hardwareProfileLabel: "Low-End Local",
		runtimeStatus: "HEALTHY",
		provisioningPercent: 100,
		compatibleEnginesReady: 11,
		compatibleEnginesTotal: 11,
		premiumEnginesReady: 0,
		premiumEnginesTotal: 8,
		benchmarksPassedPercent: 100,
		lastValidation: { relative: "2 min ago", status: "pass", at: "2026-07-06T11:58:00+00:00" },
	},
	capabilityStatuses: [
		{
			capability: "lip_sync",
			label: "Lip Sync",
			status: "partial",
			statusLabel: "PARTIAL",
			videoPipeline: true,
			referenceDisplayName: "LatentSync",
			recommendedEngineId: "wav2lip",
			currentEngineId: "wav2lip",
			readyCount: 1,
			engineCount: 2,
		},
	],
	capabilityScores: [
		{
			capability: "lip_sync",
			label: "Lip Sync",
			score: 78,
			reason: "Premium engine unavailable on current hardware",
		},
	],
	hardware: {
		profile: { label: "Low-End Local" },
		cpuModel: "AMD Ryzen 7",
		gpuName: "AMD Radeon",
		cudaAvailable: false,
		rocmAvailable: false,
		directMlAvailable: true,
		dockerGpuAccess: false,
		wsl2: true,
		ramTotalGb: 16,
		ramAvailableGb: 10,
		ramUtilization: 62,
		diskFreeGb: 1200,
		diskUtilization: 90,
		ffmpegAvailable: true,
		ollamaAvailable: true,
		pythonVersion: "3.12",
		recommendedPipeline: { lipSync: "wav2lip" },
	},
	engineRecommendations: [],
	premiumFeatures: [
		{
			engineId: "latentsync",
			displayName: "LatentSync",
			status: "blocked",
			humanReason: "Requires NVIDIA CUDA",
			needs: ["CUDA", "24 GB VRAM"],
			recommendedAlternative: "wav2lip",
		},
	],
	timeline: [
		{
			at: "2026-07-06T10:00:00+00:00",
			type: "installed",
			label: "Installed",
			detail: "Wav2Lip",
		},
	],
	warnings: [],
	recommendations: {
		summary: "Premium features remain unavailable because of hardware limitations.",
		pipeline: [{ stage: "lipSync", engineId: "wav2lip", installed: true }],
	},
	shadowCommentary: {
		speaker: "Shadow",
		message: "Your Runtime is healthy.\n\nAll compatible engines are operational.",
		paragraphs: [
			"Your Runtime is healthy.",
			"All compatible engines are operational.",
		],
	},
	maturity: {
		principle: "Configured. Verified. Measured.",
		capabilities: [],
		totalEngines: 33,
		at: "2026-07-06T12:00:00+00:00",
	},
};

describe("RuntimeHealthDashboard", () => {
	it("renders runtime health summary from dashboard API", async () => {
		vi.spyOn(runtimeService, "getDashboard").mockResolvedValue(mockDashboard);

		render(<RuntimeHealthDashboard />);

		await waitFor(() => {
			expect(screen.getByText("Overall Runtime Health")).toBeInTheDocument();
		});

		expect(screen.getAllByText("96 / 100").length).toBeGreaterThan(0);
		expect(screen.getByText("Lip Sync")).toBeInTheDocument();
		expect(screen.getByText(/Your Runtime is healthy/)).toBeInTheDocument();
		expect(screen.getAllByText(/LatentSync/).length).toBeGreaterThan(0);
	});
});
