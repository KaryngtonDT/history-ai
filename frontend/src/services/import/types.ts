export type PdfValidationResult =
	| { valid: true }
	| { valid: false; error: string };

export interface SimulateUploadOptions {
	onProgress: (progress: number) => void;
	stepMs?: number;
}

export type ImportPhase = "idle" | "uploading" | "success" | "error";
