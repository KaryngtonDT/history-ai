import { describe, expect, it } from "vitest";
import { dashboardService } from "./DashboardService";

describe("DashboardService", () => {
	it("returns dashboard data from the repository", () => {
		const data = dashboardService.getDashboard();

		expect(data.recentContents).toHaveLength(3);
		expect(data.recentContents[0]?.title).toBe("The Roman Empire");
		expect(data.statistics.artifacts).toBe(12);
	});
});
