import { DashboardHeader } from "../DashboardHeader/DashboardHeader";
import { QuickActions } from "../QuickActions/QuickActions";
import { RecentContents } from "../RecentContents/RecentContents";
import { Statistics } from "../Statistics/Statistics";
import styles from "./Dashboard.module.css";

export function Dashboard() {
	return (
		<div className={styles.root}>
			<DashboardHeader />
			<QuickActions />
			<div className={styles.grid}>
				<RecentContents />
				<Statistics />
			</div>
		</div>
	);
}
