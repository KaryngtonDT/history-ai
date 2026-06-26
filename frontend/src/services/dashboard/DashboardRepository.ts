import type { DashboardData } from "./types";

export interface DashboardRepository {
	getDashboard(): DashboardData;
}
