import { useEffect, useState } from "react";
import { EmptyState } from "@/components/ui/EmptyState";
import { Spinner } from "@/components/ui/Spinner";
import { PageIntroduction } from "@/features/product";
import type { WorkItemSummary } from "@/services/workItem/types";
import { workItemService } from "@/services/workItem/WorkItemService";
import { ActionableStats } from "../ActionableStats";
import { AIDirectorTeaser } from "../AIDirectorTeaser";
import { ContinueWork } from "../ContinueWork";
import { CreateSection } from "../CreateSection";
import { RecentWorkList } from "../RecentWorkList";
import styles from "./HomeMissionControl.module.css";

const LOAD_ERROR_MESSAGE =
	"Could not load your recent work. Check that the backend is running.";

export function HomeMissionControl() {
	const [summary, setSummary] = useState<WorkItemSummary | null>(null);
	const [loadError, setLoadError] = useState<string | null>(null);

	useEffect(() => {
		void workItemService
			.getSummary()
			.then((data) => {
				setSummary(data);
				setLoadError(null);
			})
			.catch(() => {
				setSummary({
					recentWork: [],
					continueWork: null,
					videoCount: 0,
					projectCount: 0,
					completedCount: 0,
					artifactCount: 0,
				});
				setLoadError(LOAD_ERROR_MESSAGE);
			});
	}, []);

	if (summary === null) {
		return (
			<div className={styles.root}>
				<PageIntroduction
					eyebrow="Home"
					title="History AI"
					description="Transform knowledge into understanding."
				/>
				<div className={styles.loading}>
					<Spinner label="Loading home" />
				</div>
			</div>
		);
	}

	return (
		<div className={styles.root}>
			<PageIntroduction
				eyebrow="Home"
				title="History AI"
				description="Transform knowledge into understanding."
				whatCanIDo="Upload a video, import a document, or resume work in progress."
			/>

			{loadError !== null ? (
				<EmptyState title="Unable to load home" description={loadError} />
			) : null}

			<CreateSection />

			{summary.continueWork ? (
				<ContinueWork item={summary.continueWork} />
			) : null}

			<div className={styles.columns}>
				<RecentWorkList items={summary.recentWork} />
				<div className={styles.side}>
					<ActionableStats summary={summary} />
					<AIDirectorTeaser />
				</div>
			</div>
		</div>
	);
}
