import { ROMAN_EMPIRE_CONTENT_ID } from "@/mock/artifact";
import type { ProcessingData } from "@/services/processing/types";

export const processingMocks: Record<string, ProcessingData> = {
	"1": {
		id: "1",
		contentId: ROMAN_EMPIRE_CONTENT_ID,
		title: "The Roman Empire",
		progress: 0,
		status: "pending",
		currentStep: "Pending",
		steps: [
			{ label: "Upload", completed: false, active: false },
			{ label: "Extract Text", completed: false, active: false },
			{ label: "Generate Summary", completed: false, active: false },
			{ label: "Generate Quiz", completed: false, active: false },
			{ label: "Generate Flashcards", completed: false, active: false },
		],
	},
};

export const processingMockSnapshot = {
	id: "1",
	progress: 68,
	status: "running" as const,
	currentStep: "Generating Summary",
};
