import { render, screen } from "@testing-library/react";
import { describe, expect, it, vi } from "vitest";
import { PipelineBuilder } from "./PipelineBuilder";

function selectionView(capability: string, adapterKey: string) {
	return {
		capability,
		label: capability,
		recommendedDisplayName: adapterKey,
		currentDisplayName: adapterKey,
		currentEngineId: adapterKey,
		installedEngineIds: [adapterKey],
		adapterKey,
		executable: true,
		blocked: false,
	};
}

vi.mock("@/services/runtime/RuntimeService", () => ({
	runtimeService: {
		getCapabilitySelectionView: vi
			.fn()
			.mockImplementation((capability: string) => {
				const map: Record<string, string> = {
					speech_to_text: "faster_whisper",
					translation: "ollama",
					text_to_speech: "f5_tts",
					voice_clone: "openvoice",
					lip_sync: "latentsync",
					video_render: "ffmpeg",
				};
				return Promise.resolve(
					selectionView(capability, map[capability] ?? capability),
				);
			}),
	},
}));

vi.mock("@/services/pipeline/PipelineService", () => ({
	pipelineService: {
		loadConfiguration: vi.fn().mockResolvedValue({
			id: "550e8400-e29b-41d4-a716-446655440010",
			version: 1,
			createdAt: "",
			updatedAt: "",
			stages: [
				{ stage: "speech_to_text", providerId: "faster_whisper" },
				{ stage: "translation", providerId: "ollama" },
				{ stage: "text_to_speech", providerId: "f5_tts" },
				{ stage: "voice_clone", providerId: "openvoice" },
				{ stage: "lip_sync", providerId: "latentsync" },
				{ stage: "video_render", providerId: "ffmpeg" },
			],
		}),
		saveConfiguration: vi.fn().mockResolvedValue({}),
		resetConfiguration: vi.fn().mockResolvedValue({}),
	},
}));

describe("PipelineBuilder", () => {
	it("renders pipeline stage selectors", async () => {
		render(<PipelineBuilder />);

		expect(await screen.findByText("Processing Pipeline")).toBeInTheDocument();
		expect(screen.getByLabelText("Speech-to-Text")).toBeInTheDocument();
		expect(
			screen.getByRole("button", { name: "Save Configuration" }),
		).toBeInTheDocument();
	});
});
