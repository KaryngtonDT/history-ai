import type { PdfValidationResult, SimulateUploadOptions } from "./types";

const PDF_MIME = "application/pdf";
const INVALID_FILE_MESSAGE = "Only PDF files are supported.";

export class ImportService {
	validatePdf(file: File): PdfValidationResult {
		const isPdf =
			file.type === PDF_MIME || file.name.toLowerCase().endsWith(".pdf");

		if (!isPdf) {
			return { valid: false, error: INVALID_FILE_MESSAGE };
		}

		return { valid: true };
	}

	async simulateUpload(options: SimulateUploadOptions): Promise<void> {
		const stepMs = options.stepMs ?? 80;
		const steps = [0, 20, 40, 60, 80, 100];

		for (const progress of steps) {
			options.onProgress(progress);
			if (progress < 100) {
				await new Promise((resolve) => setTimeout(resolve, stepMs));
			}
		}
	}
}

export const importService = new ImportService();
