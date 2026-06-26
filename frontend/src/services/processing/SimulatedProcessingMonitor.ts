import type {
	ProcessingMonitor,
	ProcessingUpdateCallback,
} from "./ProcessingMonitor";
import type { ProcessingRepository } from "./ProcessingRepository";
import type { ProcessingData, ProcessingStepState } from "./types";

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

const DEFAULT_STEP_MS = 900;

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

/** Mock-only monitor: replays scripted progress without a backend. */
export class SimulatedProcessingMonitor implements ProcessingMonitor {
	private readonly repository: ProcessingRepository;
	private readonly stepMs: number;

	constructor(repository: ProcessingRepository, stepMs = DEFAULT_STEP_MS) {
		this.repository = repository;
		this.stepMs = stepMs;
	}

	subscribe(jobId: string, onUpdate: ProcessingUpdateCallback): () => void {
		let active = true;
		let timeoutId: ReturnType<typeof setTimeout> | null = null;

		const stop = (): void => {
			active = false;
			if (timeoutId !== null) {
				clearTimeout(timeoutId);
				timeoutId = null;
			}
		};

		void this.repository.getProcessing(jobId).then((base) => {
			if (!active || !base) {
				stop();
				return;
			}

			let frameIndex = 0;

			const emitFrame = (): void => {
				if (!active || frameIndex >= SIMULATION_FRAMES.length) {
					stop();
					return;
				}

				const frame = SIMULATION_FRAMES[frameIndex];
				const steps =
					frame.activeIndex >= base.steps.length
						? base.steps.map((step) => ({
								...step,
								completed: true,
								active: false,
							}))
						: buildSteps(base.steps, frame.activeIndex);

				onUpdate({
					...base,
					progress: frame.progress,
					status: frame.status,
					currentStep: frame.currentStep,
					steps,
				});

				frameIndex += 1;

				if (frame.progress < 100 && active) {
					timeoutId = setTimeout(emitFrame, this.stepMs);
				} else {
					stop();
				}
			};

			emitFrame();
		});

		return stop;
	}
}
