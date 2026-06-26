import { dashboardMock } from "@/mock/dashboard";
import type { DashboardRepository } from "./DashboardRepository";
import type { DashboardData } from "./types";

export class MockDashboardRepository implements DashboardRepository {
	getDashboard(): DashboardData {
		return {
			recentContents: dashboardMock.recentContents,
			statistics: dashboardMock.statistics,
		};
	}
}
