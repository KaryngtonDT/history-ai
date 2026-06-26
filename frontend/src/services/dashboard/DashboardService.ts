import type { DashboardRepository } from "./DashboardRepository";
import { MockDashboardRepository } from "./MockDashboardRepository";
import type { DashboardData } from "./types";

export class DashboardService {
	private readonly repository: DashboardRepository;

	constructor(repository: DashboardRepository) {
		this.repository = repository;
	}

	getDashboard(): DashboardData {
		return this.repository.getDashboard();
	}
}

export const dashboardService = new DashboardService(
	new MockDashboardRepository(),
);
