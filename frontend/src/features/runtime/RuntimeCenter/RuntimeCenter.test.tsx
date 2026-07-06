import { render, screen, waitFor } from "@testing-library/react";
import userEvent from "@testing-library/user-event";
import { describe, expect, it, vi } from "vitest";
import { RuntimeCenter } from "@/features/runtime/RuntimeCenter/RuntimeCenter";
import { runtimeService } from "@/services/runtime/RuntimeService";

describe("RuntimeCenter", () => {
	it("shows hardware profile and blocked engine explanation", async () => {
		vi.spyOn(runtimeService, "getOverview").mockResolvedValue({
			principle: "Configured. Verified. Measured.",
			status: "degraded",
			health: {
				status: "degraded",
				score: 50,
				healthyEngines: 9,
				totalEngines: 18,
				issues: [],
			},
			configuration: {},
			environment: {},
		});
		vi.spyOn(runtimeService, "getReadiness").mockResolvedValue({
			status: "degraded",
			readyCount: 9,
			totalCount: 18,
			issues: [],
			engines: [
				{
					id: "latentsync",
					displayName: "LatentSync",
					capability: "lip_sync",
					status: "blocked",
					mode: "real",
					configured: true,
					discovered: false,
					executableFound: false,
					modelFound: false,
					compatibility: {
						engineId: "latentsync",
						status: "blocked",
						hardwareProfile: "low_end_local",
						blockedReasonCode: "nvidia_cuda_required",
						humanReason:
							"LatentSync requires NVIDIA CUDA with around 18 GB VRAM. This machine has AMD Radeon integrated graphics and no CUDA.",
						missingRequirements: ["NVIDIA GPU", "CUDA", "18 GB VRAM"],
						recommendedAlternative: "wav2lip",
						canBeFixedByInstall: false,
						canBeFixedByHardware: true,
						canBeFixedByRemoteProvider: true,
						severity: "blocking",
						provider: "docker",
						providerLabel: "Docker",
						fixTypeLabels: [
							"Use compatible alternative",
							"Upgrade hardware",
							"Use remote GPU provider",
						],
						hardwareCompatible: false,
					},
				},
			],
		});
		vi.spyOn(runtimeService, "getRecommendations").mockResolvedValue([]);
		vi.spyOn(runtimeService, "getHardware").mockResolvedValue({
			profile: {
				type: "low_end_local",
				label: "Low-End Local",
				summary: "Low-end local machine",
				capabilities: {
					cudaAvailable: false,
					rocmAvailable: false,
					directMlAvailable: false,
					dockerGpuAccess: false,
					wsl2: true,
					ffmpegAvailable: true,
					ollamaAvailable: true,
					gpuName: "AMD Radeon integrated graphics",
					ramTotalGb: 16,
					ramAvailableGb: 4.5,
				},
			},
			capabilities: {
				cudaAvailable: false,
				rocmAvailable: false,
				directMlAvailable: false,
				dockerGpuAccess: false,
				wsl2: true,
				ffmpegAvailable: true,
				ollamaAvailable: true,
				gpuName: "AMD Radeon integrated graphics",
				ramTotalGb: 16,
				ramAvailableGb: 4.5,
			},
			detectedAt: "2026-06-26T00:00:00+00:00",
			recommendedPipeline: {
				speech: "faster_whisper_large_v3",
				translation: "ollama_gemma3",
				tts: "f5_tts",
				voiceClone: "openvoice_v2",
				lipSync: "wav2lip",
				render: "ffmpeg_av1",
			},
		});
		vi.spyOn(runtimeService, "getCompatibility").mockResolvedValue({
			hardwareProfile: {
				type: "low_end_local",
				label: "Low-End Local",
				summary: "Low-end local machine",
				capabilities: {
					cudaAvailable: false,
					rocmAvailable: false,
					directMlAvailable: false,
					dockerGpuAccess: false,
					wsl2: true,
					ffmpegAvailable: true,
					ollamaAvailable: true,
				},
			},
			engines: [],
			blockedByHardware: ["latentsync"],
			blockedByInstall: ["wav2lip"],
			readyNow: ["ffmpeg"],
		});
		vi.spyOn(runtimeService, "getCapabilityMaturity").mockResolvedValue({
			principle: "Capability platform",
			totalEngines: 33,
			at: "2026-06-26T00:00:00+00:00",
			capabilities: [
				{
					capability: "lip_sync",
					label: "Lip Sync",
					maturity: "beta",
					maturityLabel: "Beta",
					videoPipeline: true,
					defaultEngineId: "latentsync",
					defaultDisplayName: "LatentSync",
					engineCount: 5,
					engines: [
						{
							id: "wav2lip",
							displayName: "Wav2Lip",
							role: "alternative_2",
							roleLabel: "Alternative 2",
							tier: "cpu_alternative",
							tierLabel: "CPU Alternative",
							hardware: {},
							provider: "host",
							providerLabel: "Host",
							benchmarkModel: "talking-head-10s",
						},
					],
				},
			],
		});

		render(<RuntimeCenter />);

		await waitFor(() => {
			expect(screen.getByText("Hardware Profile")).toBeInTheDocument();
		});

		expect(screen.getByText("Low-End Local")).toBeInTheDocument();
		expect(screen.getByText(/LatentSync requires NVIDIA CUDA/)).toBeInTheDocument();
		expect(screen.getByText(/Blocked by hardware/)).toBeInTheDocument();

		await userEvent.click(screen.getByRole("button", { name: "Why blocked?" }));

		expect(screen.getByText(/Recommended for this machine:/)).toBeInTheDocument();
		expect(screen.getAllByText("wav2lip").length).toBeGreaterThan(0);
		expect(screen.getByText("NVIDIA GPU")).toBeInTheDocument();
		expect(screen.getByText(/Capability Maturity \(33 engines\)/)).toBeInTheDocument();
	});
});
