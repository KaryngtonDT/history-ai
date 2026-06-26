export interface DashboardContent {
	id: string;
	title: string;
	sourceType: "pdf" | "audio" | "video" | "youtube";
	status: "processing" | "completed";
	progress: number;
}

export interface DashboardStatistics {
	contents: number;
	completed: number;
	processing: number;
	artifacts: number;
}

export interface DashboardData {
	recentContents: DashboardContent[];
	statistics: DashboardStatistics;
}
