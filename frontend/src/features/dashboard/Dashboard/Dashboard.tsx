import { useEffect, useState } from "react";
import { Spinner } from "@/components/ui/Spinner";
import { contentService } from "@/services/content/ContentService";
import type { DashboardView } from "@/services/content/types";
import { DashboardHeader } from "../DashboardHeader/DashboardHeader";
import { QuickActions } from "../QuickActions/QuickActions";
import { RecentContents } from "../RecentContents/RecentContents";
import { Statistics } from "../Statistics/Statistics";
import styles from "./Dashboard.module.css";

export function Dashboard() {
	const [dashboard, setDashboard] = useState<DashboardView | null>(null);

	useEffect(() => {
		void contentService.getDashboardData().then(setDashboard);
	}, []);

	if (dashboard === null) {
		return (
			<div className={styles.root}>
				<DashboardHeader />
				<div className={styles.loading}>
					<Spinner label="Loading dashboard" />
				</div>
			</div>
		);
	}

	const { recentContents, statistics } = dashboard;

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
