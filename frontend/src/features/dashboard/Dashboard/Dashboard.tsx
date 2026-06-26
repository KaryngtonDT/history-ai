import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { contentService } from "@/services/content/ContentService";
import type { DashboardView } from "@/services/content/domain/Content";
import { DashboardHeader } from "../DashboardHeader/DashboardHeader";
import { QuickActions } from "../QuickActions/QuickActions";
import { RecentContents } from "../RecentContents/RecentContents";
import { Statistics } from "../Statistics/Statistics";
import styles from "./Dashboard.module.css";

const LOAD_ERROR_MESSAGE =
	"Could not reach the server. Check that the backend is running.";

export function Dashboard() {
	const [dashboard, setDashboard] = useState<DashboardView | null>(null);
	const [loadError, setLoadError] = useState<string | null>(null);

	useEffect(() => {
		void contentService
			.getDashboardData()
			.then((data) => {
				setDashboard(data);
				setLoadError(null);
			})
			.catch(() => {
				setDashboard({
					recentContents: [],
					statistics: {
						contents: 0,
						completed: 0,
						processing: 0,
						artifacts: 0,
					},
				});
				setLoadError(LOAD_ERROR_MESSAGE);
			});
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
			{loadError !== null ? (
				<EmptyState title="Unable to load dashboard" description={loadError} />
			) : (
				<div className={styles.grid}>
					{recentContents.length === 0 ? (
						<EmptyState
							title="No content yet"
							description="Import your first PDF."
						/>
					) : (
						<RecentContents contents={recentContents} />
					)}
					<Statistics statistics={statistics} />
				</div>
			)}
		</div>
	);
}
