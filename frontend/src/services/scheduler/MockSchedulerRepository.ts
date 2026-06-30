import type { ExecutionSchedule } from "./types";

export const MOCK_PREVIEW_SCHEDULE: ExecutionSchedule = {
	id: "550e8400-e29b-41d4-a716-446655440030",
	strategy: "balanced",
	estimatedCompletionSeconds: 360,
	currentStage: "voice_clone",
	currentResource: "gpu",
	stages: [
		{
			stage: "speech_to_text",
			order: 1,
			status: "completed",
			estimatedDurationSeconds: 60,
			parallelGroup: 1,
			requirements: [{ type: "gpu", weight: 1 }],
		},
		{
			stage: "translation",
			order: 2,
			status: "completed",
			estimatedDurationSeconds: 30,
			parallelGroup: 2,
			requirements: [{ type: "cpu", weight: 1 }],
		},
		{
			stage: "text_to_speech",
			order: 3,
			status: "completed",
			estimatedDurationSeconds: 45,
			parallelGroup: 3,
			requirements: [{ type: "gpu", weight: 1 }],
		},
		{
			stage: "voice_clone",
			order: 4,
			status: "running",
			estimatedDurationSeconds: 120,
			parallelGroup: 4,
			requirements: [{ type: "gpu", weight: 1 }],
		},
		{
			stage: "lip_sync",
			order: 5,
			status: "pending",
			estimatedDurationSeconds: 90,
			parallelGroup: 5,
			requirements: [{ type: "gpu", weight: 1 }],
		},
		{
			stage: "video_render",
			order: 6,
			status: "pending",
			estimatedDurationSeconds: 90,
			parallelGroup: 6,
			requirements: [
				{ type: "cpu", weight: 1 },
				{ type: "io", weight: 1 },
			],
		},
	],
	resources: [
		{ type: "cpu", running: 0, pending: 1, maxConcurrency: 2 },
		{ type: "gpu", running: 1, pending: 2, maxConcurrency: 1 },
		{ type: "io", running: 0, pending: 1, maxConcurrency: 4 },
	],
};

export class MockSchedulerRepository {
	async getPreviewSchedule(): Promise<ExecutionSchedule> {
		return MOCK_PREVIEW_SCHEDULE;
	}

	async getByVideoId(videoId: string): Promise<ExecutionSchedule> {
		return {
			...MOCK_PREVIEW_SCHEDULE,
			id: "550e8400-e29b-41d4-a716-446655440031",
			videoId,
		};
	}
}
