export type ProcessingStatus = "pending" | "running" | "completed";

export interface ProcessingStepState {
	label: string;
	completed: boolean;
	active: boolean;
}

export interface ProcessingData {
	id: string;
	title: string;
	progress: number;
	status: ProcessingStatus;
	currentStep: string;
	steps: ProcessingStepState[];
}

export interface SimulateProcessingOptions {
	onUpdate: (data: ProcessingData) => void;
	stepMs?: number;
}
