/**
 * Frontend Content aggregate — aligned with backend Domain\Content.
 * `progress` is a UI field until ProcessingJob is connected (Sprint 2+).
 */

export type ContentSourceType = "pdf" | "audio" | "video" | "youtube";

export type ContentDisplayStatus = "processing" | "completed";

export interface Content {
	id: string;
	title: string;
	sourceType: ContentSourceType;
	status: ContentDisplayStatus;
	progress: number;
}

export interface ContentStatistics {
	contents: number;
	completed: number;
	processing: number;
	artifacts: number;
}

export interface DashboardView {
	recentContents: Content[];
	statistics: ContentStatistics;
}

export interface LibraryView {
	contents: Content[];
}

export interface CreateContentInput {
	title: string;
	sourceType: ContentSourceType;
}

export interface CreateContentResult {
	id: string;
}

export type PdfValidationResult =
	| { valid: true }
	| { valid: false; error: string };

export interface SimulateUploadOptions {
	onProgress: (progress: number) => void;
	stepMs?: number;
}
