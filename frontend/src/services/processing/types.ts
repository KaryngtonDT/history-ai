export type ProcessingStatus =
	| "pending"
	| "running"
	| "completed"
	| "failed"
	| "cancelled";

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

export interface ProcessingJobApiDto {
	id: string;
	contentId: string;
	type: string;
	status: string;
	progress: number;
	startedAt: string | null;
	completedAt: string | null;
	failedAt: string | null;
}

const TYPE_LABELS: Record<string, string> = {
	summary: "Summary",
	quiz: "Quiz",
	flashcards: "Flashcards",
	translation: "Translation",
	podcast: "Podcast",
	timeline: "Timeline",
	mind_map: "Mind Map",
};

const DEFAULT_STEP_LABELS = [
	"Upload",
	"Extract Text",
	"Generate Summary",
	"Generate Quiz",
	"Generate Flashcards",
];

function normalizeStatus(status: string): ProcessingStatus {
	if (
		status === "pending" ||
		status === "running" ||
		status === "completed" ||
		status === "failed" ||
		status === "cancelled"
	) {
		return status;
	}

	return "pending";
}

function currentStepFromStatus(status: ProcessingStatus): string {
	switch (status) {
		case "pending":
			return "Pending";
		case "running":
			return "Processing";
		case "completed":
			return "Completed";
		case "failed":
			return "Failed";
		case "cancelled":
			return "Cancelled";
	}
}

function deriveSteps(
	status: ProcessingStatus,
	progress: number,
): ProcessingStepState[] {
	if (status === "pending") {
		return DEFAULT_STEP_LABELS.map((label) => ({
			label,
			completed: false,
			active: false,
		}));
	}

	if (status === "completed") {
		return DEFAULT_STEP_LABELS.map((label) => ({
			label,
			completed: true,
			active: false,
		}));
	}

	const activeIndex = Math.min(
		DEFAULT_STEP_LABELS.length - 1,
		Math.floor(progress / (100 / DEFAULT_STEP_LABELS.length)),
	);

	return DEFAULT_STEP_LABELS.map((label, index) => ({
		label,
		completed: index < activeIndex,
		active: index === activeIndex,
	}));
}

export function mapProcessingFromApi(dto: ProcessingJobApiDto): ProcessingData {
	const status = normalizeStatus(dto.status);
	const typeLabel = TYPE_LABELS[dto.type] ?? dto.type;

	return {
		id: dto.id,
		title: `${typeLabel} processing`,
		progress: dto.progress,
		status,
		currentStep: currentStepFromStatus(status),
		steps: deriveSteps(status, dto.progress),
	};
}
