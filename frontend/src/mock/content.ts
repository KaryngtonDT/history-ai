import type { Content } from "@/services/content/types";

export const contentMock: { contents: Content[] } = {
	contents: [
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
	],
};
