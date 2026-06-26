import type {
	DashboardContent,
	DashboardStatistics,
} from "@/services/dashboard/types";

export const dashboardMock = {
	recentContents: [
		{
			id: "1",
			title: "The Roman Empire",
			sourceType: "pdf",
			status: "processing",
			progress: 62,
		},
		{
			id: "2",
			title: "French Revolution",
			sourceType: "pdf",
			status: "completed",
			progress: 100,
		},
		{
			id: "3",
			title: "Industrial Revolution",
			sourceType: "youtube",
			status: "completed",
			progress: 100,
		},
	] satisfies DashboardContent[],

	statistics: {
		contents: 3,
		completed: 2,
		processing: 1,
		artifacts: 12,
	} satisfies DashboardStatistics,
};
