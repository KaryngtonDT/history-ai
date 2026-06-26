import { dashboardService } from "@/services/dashboard/DashboardService";
import { DashboardHeader } from "../DashboardHeader/DashboardHeader";
import { QuickActions } from "../QuickActions/QuickActions";
import { RecentContents } from "../RecentContents/RecentContents";
import { Statistics } from "../Statistics/Statistics";
import styles from "./Dashboard.module.css";

export function Dashboard() {
	const { recentContents, statistics } = dashboardService.getDashboard();

	return (
		<div className={styles.root}>
			<DashboardHeader />
			<QuickActions />
			<div className={styles.grid}>
				<RecentContents contents={recentContents} />
				<Statistics statistics={statistics} />
			</div>
		</div>
	);
}
