import { MockProcessingRepository } from "./MockProcessingRepository";
import type { ProcessingRepository } from "./ProcessingRepository";
import type {
	ProcessingData,
	ProcessingStepState,
	SimulateProcessingOptions,
} from "./types";

const SIMULATION_FRAMES: {
	progress: number;
	status: ProcessingData["status"];
	currentStep: string;
	activeIndex: number;
}[] = [
	{ progress: 0, status: "pending", currentStep: "Pending", activeIndex: -1 },
	{ progress: 12, status: "running", currentStep: "Uploading", activeIndex: 0 },
	{
		progress: 28,
		status: "running",
		currentStep: "Extracting Text",
		activeIndex: 1,
	},
	{
		progress: 52,
		status: "running",
		currentStep: "Generating Summary",
		activeIndex: 2,
	},
	{
		progress: 76,
		status: "running",
		currentStep: "Generating Quiz",
		activeIndex: 3,
	},
	{
		progress: 92,
		status: "running",
		currentStep: "Generating Flashcards",
		activeIndex: 4,
	},
	{
		progress: 100,
		status: "completed",
		currentStep: "Completed",
		activeIndex: 5,
	},
];

function buildSteps(
	baseSteps: ProcessingStepState[],
	activeIndex: number,
): ProcessingStepState[] {
	return baseSteps.map((step, index) => ({
		label: step.label,
		completed: activeIndex >= baseSteps.length || index < activeIndex,
		active: index === activeIndex,
	}));
}

export class ProcessingService {
	private readonly repository: ProcessingRepository;

	constructor(repository: ProcessingRepository) {
		this.repository = repository;
	}

	getProcessing(id: string): ProcessingData | null {
		return this.repository.getProcessing(id);
	}

	async simulateProcessing(
		id: string,
		options: SimulateProcessingOptions,
	): Promise<void> {
		const base = this.repository.getProcessing(id);
		if (!base) {
			return;
		}

		const stepMs = options.stepMs ?? 900;

		for (const frame of SIMULATION_FRAMES) {
			const steps =
				frame.activeIndex >= base.steps.length
					? base.steps.map((step) => ({
							...step,
							completed: true,
							active: false,
						}))
					: buildSteps(base.steps, frame.activeIndex);

			options.onUpdate({
				...base,
				progress: frame.progress,
				status: frame.status,
				currentStep: frame.currentStep,
				steps,
			});

			if (frame.progress < 100) {
				await new Promise((resolve) => setTimeout(resolve, stepMs));
			}
		}
	}
}

export const processingService = new ProcessingService(
	new MockProcessingRepository(),
);
