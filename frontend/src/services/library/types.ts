export type LibraryContentStatus = "processing" | "completed";
export type LibrarySourceType = "pdf" | "audio" | "video" | "youtube";

export interface LibraryContent {
	id: string;
	title: string;
	sourceType: LibrarySourceType;
	status: LibraryContentStatus;
	progress: number;
}

export interface LibraryData {
	contents: LibraryContent[];
}
